from mosip_auth_sdk.models import DemographicsModel
from mosip_auth_sdk import MOSIPAuthenticator
from dynaconf import Dynaconf
from face_verify import test_face
import numpy as np
import tempfile
import base64
import cv2
import os

TEST_UIN = "5408602380"  # Yuki Nakashima
# TEST_UIN = "7903740631"  # Haruka Kudou

HARDCODED_IMAGE = "yuki.jpg"
# HARDCODED_IMAGE = "haruka.jpg"

config = Dynaconf(settings_files=["./config.toml"], environments=False)
authenticator = MOSIPAuthenticator(config=config)

# kyc auth
demographics_data = DemographicsModel(
    name=[{"language": "eng", "value": "Yuki Nakashima"}],
    address_line1=[{"language": "eng", "value": "UP AECH"}],
)
response = authenticator.kyc(
    individual_id=TEST_UIN,
    individual_id_type="UIN",
    demographic_data=demographics_data,
    consent=True,
)
response_body = response.json()

# if not response_body.get("response", {}).get("kycStatus"):
#     print(f"KYC failed: {response_body.get('errors')}")
#     exit(1)

decrypted_response = authenticator.decrypt_response(response_body)
face_bytes = base64.b64decode(decrypted_response.pop("photo"))

# shows demographic data
print(f"DECRYPTED RESPONSE: {decrypted_response}")

# attempt to decode image from face_bytes
img = None
offsets_to_try = [i for i in range(70, 85)]
for offset in offsets_to_try:
    face_as_np = np.frombuffer(face_bytes[offset:], dtype=np.uint8)
    img = cv2.imdecode(face_as_np, cv2.IMREAD_COLOR)
    if img is not None:
        print(f"Found valid image at offset {offset}")
        break

if img is None:
    print("ERROR: Could not decode image")
    exit(1)

local_img = cv2.imread(HARDCODED_IMAGE)

# show image in window and save to file when 's' is pressed
cv2.imshow("MOSIP Photo", img)
cv2.imshow("Local Image", local_img)
k = cv2.waitKey(0)

if k == ord("s"):
    cv2.imwrite("./mosip.jpg", img)
    mosip_photo_path = "./mosip.jpg"

print(f"Comparing: MOSIP photo vs {HARDCODED_IMAGE}")
try:
    test_face(mosip_photo_path, HARDCODED_IMAGE)
finally:
    os.unlink(mosip_photo_path)

print("\nPress any key in either image window to close...")
cv2.waitKey(0)
cv2.destroyAllWindows()
