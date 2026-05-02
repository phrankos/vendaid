import base64
import os
import tempfile

import cv2
import numpy as np
from deepface import DeepFace
from dynaconf import Dynaconf
from flask import Flask, jsonify, request
from flask_cors import CORS
from mosip_auth_sdk import MOSIPAuthenticator
from mosip_auth_sdk.models import DemographicsModel

_DIR = os.path.dirname(os.path.abspath(__file__))
os.chdir(_DIR)

config = Dynaconf(settings_files=["./config.toml"], environments=False)
authenticator = MOSIPAuthenticator(config=config)

app = Flask(__name__)
CORS(app)


def _decode_mosip_photo(face_bytes: bytes):
    for offset in range(70, 85):
        face_as_np = np.frombuffer(face_bytes[offset:], dtype=np.uint8)
        img = cv2.imdecode(face_as_np, cv2.IMREAD_COLOR)
        if img is not None:
            return img
    return None


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


@app.route("/verify", methods=["POST"])
def verify():
    data = request.get_json(force=True, silent=True) or {}
    printable = {k: (v if k != "image_base64" else f"<{len(v)} chars>") for k, v in data.items()}
    print(f"[/verify] scanned data: {printable}", flush=True)
    uin = data.get("uin")
    name = data.get("name")
    image_b64 = data.get("image_base64")

    if not uin or not name:
        print(f"[/verify] missing fields — uin={uin!r} name={name!r}", flush=True)
        return jsonify({"error": "uin and name are required", "received": printable}), 400

    demographics = DemographicsModel(name=[{"language": "eng", "value": name}])

    try:
        response = authenticator.kyc(
            individual_id=str(uin),
            individual_id_type="UIN",
            demographic_data=demographics,
            consent=True,
        )
        body = response.json()
    except Exception as e:
        return jsonify({"verified": False, "error": str(e)}), 502

    if not body.get("response", {}).get("kycStatus"):
        return jsonify({
            "verified": False,
            "errors": body.get("errors", []),
        }), 200

    try:
        decrypted = authenticator.decrypt_response(body)
    except Exception as e:
        return jsonify({"verified": True, "kyc_data": {}, "decrypt_error": str(e)}), 200

    photo_b64 = decrypted.pop("photo", None)
    result = {"verified": True, "kyc_data": decrypted}

    if image_b64 and photo_b64:
        result["face_match"] = _run_face_match(photo_b64, image_b64)

    return jsonify(result), 200


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
            model_name="ArcFace",
            detector_backend="mtcnn",
            distance_metric="cosine",
        )
        return {
            "verified": bool(df["verified"]),
            "distance": float(df["distance"]),
            "threshold": float(df["threshold"]),
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
