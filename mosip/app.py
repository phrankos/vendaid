import base64
import json
import os
import tempfile
import time
import urllib3
from concurrent.futures import ThreadPoolExecutor, TimeoutError as FuturesTimeoutError

import cv2
import numpy as np
import requests as _http
from deepface import DeepFace
from dynaconf import Dynaconf
from flask import Flask, Response, jsonify, request, stream_with_context
from flask_cors import CORS
from mosip_auth_sdk import MOSIPAuthenticator
from mosip_auth_sdk.models import DemographicsModel

_DIR = os.path.dirname(os.path.abspath(__file__))
os.chdir(_DIR)

config = Dynaconf(settings_files=["./config.toml"], environments=False)

MOCK_ENABLED = config.mock.enabled
MOCK_URL     = config.mock.url.rstrip("/")

# Always initialize so tests can patch authenticator regardless of mode.
authenticator = MOSIPAuthenticator(config=config)

if MOCK_ENABLED:
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

KYC_ATTEMPT_TIMEOUT_S = config.kyc.attempt_timeout_s
KYC_MAX_RETRIES       = config.kyc.max_retries
KYC_RETRY_DELAY_S     = config.kyc.retry_delay_s

app = Flask(__name__)
CORS(app)


def _decode_mosip_photo(face_bytes: bytes):
    for offset in range(70, 85):
        face_as_np = np.frombuffer(face_bytes[offset:], dtype=np.uint8)
        img = cv2.imdecode(face_as_np, cv2.IMREAD_COLOR)
        if img is not None:
            return img
    return None


_MOCK_DEMO_FIELDS = frozenset([
    "gender", "age", "phone_number", "email_id", "postal_code",
    "location1", "location3", "zone",
    "address_line1", "address_line2", "address_line3",
])


def _kyc_mock(uin: str, name: str, dob: str | None = None, extra: dict | None = None) -> dict:
    """Call the prof's mock server. Returns the parsed JSON body (already decrypted)."""
    payload = {"individual_id": uin, "consent": True, "name": name}
    if dob:
        # Mock server expects YYYY/MM/DD; callers may pass YYYY-MM-DD or YYYY/MM/DD.
        payload["dob"] = dob.replace("-", "/")
    if extra:
        for k, v in extra.items():
            if k in _MOCK_DEMO_FIELDS:
                payload[k] = v
    resp = _http.post(
        f"{MOCK_URL}/api/v1/auth/kyc",
        json=payload,
        verify=False,
        timeout=KYC_ATTEMPT_TIMEOUT_S,
    )
    return resp.json()


def _do_kyc_call(uin: str, name: str, dob: str | None, demographics, extra: dict | None = None) -> dict:
    """Submit a KYC request and return the parsed JSON body as a dict.

    Abstracts mock vs real MOSIP so tests can patch a single function.
    """
    if MOCK_ENABLED:
        return _kyc_mock(uin, name, dob, extra)
    resp = authenticator.kyc(
        individual_id=uin,
        individual_id_type="UIN",
        demographic_data=demographics,
        consent=True,
    )
    return resp.json()


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


@app.route("/verify", methods=["POST"])
def verify():
    data = request.get_json(force=True, silent=True) or {}
    printable = {k: (v if k != "image_base64" else f"<{len(v)} chars>") for k, v in data.items()}
    print(f"[/verify] scanned data: {printable}", flush=True)
    uin       = data.get("uin")
    name      = data.get("name")
    dob       = data.get("dob")
    image_b64 = data.get("image_base64")
    tag = f"[kyc uin={uin}]"

    # Extra demographic fields forwarded from the QR code (mock server accepts all of them).
    extra = {k: data[k] for k in _MOCK_DEMO_FIELDS if k in data}

    if not uin or not name:
        print(f"[/verify] missing fields — uin={uin!r} name={name!r}", flush=True)
        return jsonify({"error": "uin and name are required", "received": printable}), 400

    demographics = DemographicsModel(name=[{"language": "eng", "value": name}]) \
        if not MOCK_ENABLED else None

    def generate():
        executor = ThreadPoolExecutor(max_workers=KYC_MAX_RETRIES)
        try:
            for attempt in range(1, KYC_MAX_RETRIES + 1):
                future = executor.submit(_do_kyc_call, str(uin), name, dob, demographics, extra)
                try:
                    body = future.result(timeout=KYC_ATTEMPT_TIMEOUT_S)
                except FuturesTimeoutError:
                    print(f"{tag} attempt {attempt}/{KYC_MAX_RETRIES} timed out after {KYC_ATTEMPT_TIMEOUT_S}s", flush=True)
                    yield json.dumps({"uin": uin, "status": "waiting", "attempt": attempt}) + "\n"
                    if attempt < KYC_MAX_RETRIES:
                        print(f"{tag} retrying in {KYC_RETRY_DELAY_S}s...", flush=True)
                        time.sleep(KYC_RETRY_DELAY_S)
                    continue
                except Exception as e:
                    print(f"{tag} error: {e}", flush=True)
                    yield json.dumps({"uin": uin, "verified": False, "error": str(e)}) + "\n"
                    return

                print(f"{tag} attempt {attempt} responded", flush=True)
                if not body.get("response", {}).get("kycStatus"):
                    print(f"{tag} kycStatus false — errors: {body.get('errors', [])}", flush=True)
                    yield json.dumps({"uin": uin, "verified": False, "errors": body.get("errors") or []}) + "\n"
                    return

                # Extract KYC data. Mock response is already plain; real MOSIP is encrypted.
                if MOCK_ENABLED:
                    kyc_data = {
                        k: v for k, v in body["response"].items()
                        if k not in ("kycStatus", "authToken")
                    }
                    photo_b64 = kyc_data.pop("photo", None)
                else:
                    try:
                        kyc_data = authenticator.decrypt_response(body)
                    except Exception as e:
                        yield json.dumps({"uin": uin, "verified": True, "kyc_data": {}, "decrypt_error": str(e)}) + "\n"
                        return
                    photo_b64 = kyc_data.pop("photo", None)

                result = {"uin": uin, "verified": True, "kyc_data": kyc_data}
                if image_b64 and photo_b64:
                    result["face_match"] = _run_face_match(photo_b64, image_b64)

                yield json.dumps(result) + "\n"
                return

            print(f"{tag} all {KYC_MAX_RETRIES} attempts timed out", flush=True)
            yield json.dumps({"uin": uin, "verified": False, "error": "MOSIP timeout after all retries"}) + "\n"
        finally:
            executor.shutdown(wait=False)

    return Response(stream_with_context(generate()), content_type="application/x-ndjson")


def _run_face_match(mosip_photo_b64: str, live_image_b64: str) -> dict:
    face_bytes = base64.b64decode(mosip_photo_b64)
    mosip_img = _decode_mosip_photo(face_bytes)
    if mosip_img is None:
        return {"verified": False, "error": "could not decode MOSIP photo"}

    try:
        live_bytes = base64.b64decode(live_image_b64)
    except Exception as e:
        return {"verified": False, "error": f"invalid base64 for image_base64: {e}"}

    mosip_tmp = tempfile.NamedTemporaryFile(suffix=".jpg", delete=False)
    live_tmp = tempfile.NamedTemporaryFile(suffix=".jpg", delete=False)
    try:
        cv2.imwrite(mosip_tmp.name, mosip_img)
        live_tmp.write(live_bytes)
        live_tmp.close()
        df = DeepFace.verify(
            img1_path=mosip_tmp.name,
            img2_path=live_tmp.name,
            model_name=config.face.model,
            detector_backend="mtcnn",
            distance_metric=config.face.distance_metric,
        )
        distance  = float(df["distance"])
        threshold = float(config.face.threshold)
        return {
            "verified": distance <= threshold,
            "distance": distance,
            "threshold": threshold,
        }
    except Exception as e:
        return {"verified": False, "error": str(e)}
    finally:
        for path in (mosip_tmp.name, live_tmp.name):
            try:
                os.unlink(path)
            except OSError:
                pass


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=False)
