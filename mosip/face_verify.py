from deepface import DeepFace
import sys

_original_unraisablehook = sys.unraisablehook
def _unraisablehook(unraisable):
    if "I/O operation on closed file" in str(unraisable.exc_value):
        return
    _original_unraisablehook(unraisable)
sys.unraisablehook = _unraisablehook

def test_face(img1_path, img2_path):
    try:
        result = DeepFace.verify(
            img1_path=img1_path,
            img2_path=img2_path,
            model_name="ArcFace",
            # model_name="VGG-face",
            # detector_backend="opencv",
            detector_backend="mtcnn",
            # detector_backend="skip",
            distance_metric="cosine",
        )
        verdict = "MATCH" if result["verified"] else "NOT MATCH"
        print(f"{verdict} | distance: {result['distance']:.4f} | threshold: {result['threshold']}")
    except Exception as e:
        msg = str(e)
        if "img1_path" in msg:
            print(f"Face detection failed on MOSIP photo: {e}")
        elif "img2_path" in msg:
            print(f"Face detection failed on local image ({img2_path}): {e}")
        else:
            print(f"Face verification error: {e}")

if __name__ == "__main__":
    img1 = sys.argv[1]
    img2 = sys.argv[2]
    test_face(img1, img2)