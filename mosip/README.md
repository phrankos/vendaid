## Cmds
### Wireguard

sudo cp mosip/wireguard-config.txt /etc/wireguard/mosip.conf
sudo wg-quick up mosip
<!-- shutdown -->
sudo wg-quick down mosip

### db

php artisan migrate

### Flask

cd mosip
./env/bin/python app.py

uses 127.0.0.1:5000

### Laravel

php artisan serve

uses 127.0.0.1:8000


## Architecture (one-line summary)

The wemos sends the QR JSON + the base64-encoded ESP32 photo into a single POST to Laravel /api/scan. Laravel forwards uin, name, and image_base64 to Flask /verify. Flask calls MOSIP KYC and runs DeepFace.


## Tests

### Sanity check

curl http://127.0.0.1:5000/health

Expected: {"status":"ok"}.

### Flask /verify, KYC only (no face match)

curl -X POST http://127.0.0.1:5000/verify -H "Content-Type: application/json" -d '{"uin":"5408602380","name":"Yuki Nakashima"}'

Expected: verified true

curl -X POST http://127.0.0.1:5000/verify -H "Content-Type: application/json" -d '{"uin":"5423602380","name":"Yuki Nakashima"}'

Expected: verified false. 


### Flask /verify, KYC + face match

Pass image_base64. Flask runs MOSIP KYC, then DeepFace verifies the live image against the MOSIP photo. 

B64=$(base64 -i mosip/yuki.jpg)
curl -X POST http://127.0.0.1:5000/verify -H "Content-Type: application/json" -d "{\"uin\":\"5408602380\",\"name\":\"Yuki Nakashima\",\"image_base64\":\"$B64\"}"

Expected: face_match.verified true with a distance and threshold, verified true, kyc_data populated


B64=$(base64 -i mosip/haruka.jpg)
curl -X POST http://127.0.0.1:5000/verify -H "Content-Type: application/json" -d "{\"uin\":\"5408602380\",\"name\":\"Yuki Nakashima\",\"image_base64\":\"$B64\"}"

Expected: face_match.verified false with a distance and threshold, verified true, kyc_data populated

### Laravel /api/scan

Will be used by wemos. Laravel forwards uin and name to Flask /verify, gets KYC back, saves the patient.

curl -X POST http://127.0.0.1:8000/api/scan -H "Content-Type: application/json" -d '{"uin":"5408602380","name":"Yuki Nakashima","dob":"1997/09/12","location1":"Quezon City"}'

Expected: status success, mosip_verified true, found true. face_match will be null. 

The patient row is created. to verify:
artisan tinker --execute="echo App\Models\Patient::where('scan_id','5408602380')->first()"

with image: 

B64=$(base64 -i mosip/haruka.jpg)
curl -X POST http://127.0.0.1:8000/api/scan -H "Content-Type: application/json" -d "{\"uin\":\"5408602380\",\"name\":\"Yuki Nakashima\",\"dob\":\"1997/09/12\",\"location1\":\"Quezon City\",\"image_base64\":\"$B64\"}"

B64=$(base64 -i mosip/.jpg)
curl -X POST http://127.0.0.1:8000/api/scan -H "Content-Type: application/json" -d "{\"uin\": \"7401478198\", \"name\": \"Xiryl Vinz Rentino\", \"dob\": \"2005/06/22\", \"file\": \"xiryl.png\", \"location1\": \"Quezon City\", \"image_base64\": \"$B64\"}"
Expected: status success, mosip_verified true, face_match.verified true.



## Reset for tests
artisan tinker --execute="App\Models\Patient::where('scan_id','5408602380')->delete();"
alias artisan="php -d 'error_reporting=E_ALL & ~E_DEPRECATED' artisan"



## Files added or changed

mosip/app.py: Flask service on 127.0.0.1:5000.
- /health: sanity check.
- /verify: takes uin, name, and image_base64. Calls MOSIP KYC and decrypts the response. 

database/migrations/2026_04_25_120000_add_mosip_fields_to_patients_table.php: Adds birthdate and barangay columns. Marks scan_id as unique (for MOSIP uin). Relaxes scan_id, sex_id, created_by, and updated_by to nullable so a patient can be auto-created from a MOSIP scan without those fields. (to follow: can get sex from mosip kyc data)

app/Models/Patient.php (changed): Added the new columns (birthdate, barangay) to fillable, and added a date cast for birthdate.

app/Http/Controllers/MainSystemController.php: receive() requires JSON with a uin. FLOW: parse the QR, validate dob format, calls Flask /verify on every scan, rejects if face_match comes back unverified, saves or updates the patient by scan_id, then looks up the latest non-expired prescription. (to follow: error for when prescription is expired)

{"uin": "7401478198", "name": "Xiryl Vinz Rentino", "dob": "2005/06/22", "file": "xiryl.png", "address_line1": "Commonwealth Ave.", "address_line2": "Philcoa", "address_line3": "QC Circle", "location1": "Quezon City", "location3": "Metropolitan Manila Second District", "zone": "Central", "postal_code": "11100"}


<!-- identity 
valid presciption - valid + in stock -->

