
// Wiring (QR scanner GM861S):
//   TXD -> D5, RXD -> D6, 5V -> VIN, GND -> GND
//
// Wiring (HC-SR04 ultrasonic):
//   VCC -> 5V, GND -> GND
//   TRIG -> D9
//   ECHO -> voltage divider -> D10
//
// Motor commands sent wirelessly to D1 B via ESP-NOW (no wires needed)

//  QR Scanner (GM861S)

//   ┌─────────────┬─────────────┐
//   │ Scanner Pin │ D1 Mini Pin │
//   ├─────────────┼─────────────┤
//   │ TXD         │ D5 (GPIO14) │
//   ├─────────────┼─────────────┤
//   │ RXD         │ D6 (GPIO12) │
//   ├─────────────┼─────────────┤
//   │ 5V          │ VIN         │
//   ├─────────────┼─────────────┤
//   │ GND         │ GND         │
//   └─────────────┴─────────────┘

//   HC-SR04 Ultrasonic

//   ┌────────────┬──────────────────────────────────┐
//   │ Sensor Pin │           D1 Mini Pin            │
//   ├────────────┼──────────────────────────────────┤
//   │ VCC        │ 5V                               │
//   ├────────────┼──────────────────────────────────┤
//   │ GND        │ GND                              │
//   ├────────────┼──────────────────────────────────┤
//   │ TRIG       │ D9 (GPIO2)                       │
//   ├────────────┼──────────────────────────────────┤
//   │ ECHO       │ D10 (GPIO15) via voltage divider │


#include <SoftwareSerial.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <espnow.h>

// ── Pins ─────────────────────────────────────────────────────────────────────
#define SCANNER_RX D5   // GPIO14
#define SCANNER_TX D6   // GPIO12
#define TRIG_PIN   D9   // GPIO2
#define ECHO_PIN   D10  // GPIO15

// ── D1 B MAC address ──────────────────────────────────────────────────────────
uint8_t D1B_MAC[] = {0xD8, 0xBF, 0xC0, 0xF9, 0x8D, 0x2B};

//── Config ────────────────────────────────────────────────────────────────────
// const char* WIFI_SSID = "Converge_2.4GHz_fG6m";
// const char* WIFI_PASS = "eQZxC6sD";
// const char* WIFI_SSID = "Ethan";
// const char* WIFI_PASS = "password123";
// const char* WIFI_SSID = "hootspoot";
// const char* WIFI_PASS = "hotdogspot";
// const char* SCAN_URL = "http://10.107.43.167:8000/api/scan";
// const char* DISPENSED_URL = "http://10.107.43.167:8000/api/dispensed";

// const char* SCAN_URL = "http://10.147.36.230:8000/api/scan";
// const char* DISPENSED_URL = "http://10.147.36.230:8000/api/dispensed";

const char* WIFI_SSID = "aclwifi";
const char* WIFI_PASS = "@cl6rouP";
const char* SCAN_URL  = "http://192.168.60.172:8000/api/test-dispense";
// const char* SCAN_URL = "http://192.168.60.172:8000/api/scan";
const char* DISPENSED_URL = "http://192.168.60.172:8000/api/dispensed";

// const char* WIFI_SSID = "Mon";
// const char* WIFI_PASS = "xiaobao1";
// // const char* SCAN_URL = "http://172.20.10.3:8000/api/scan";
// const char* SCAN_URL = "http://172.20.10.3:8000/api/test-dispense";
// const char* DISPENSED_URL = "http://172.20.10.3:8000/api/dispensed";


// const char* SCAN_URL      = "http://192.168.60.164:8000/api/scan";
// const char* SCAN_URL = "http://172.20.10.3:8000/api/scan";
// const char* DISPENSED_URL = "http://172.20.10.3:8000/api/dispensed";

// const char* SCAN_URL  = "http://10.147.36.230:8000/api/test-dispense";

const bool TEST_MODE = false;  // set true when using test-dispense endpoint

const unsigned long POST_TIMEOUT_MS       = 300000;
const unsigned long SCAN_TRIGGER_WAIT_MS  = 1500;  // max wait for scanner to start sending
const unsigned long INTER_BYTE_TIMEOUT_MS = 300;
const unsigned long COOLDOWN_MS           = 5000;
const unsigned long DISPENSE_TIMEOUT_MS   = 15000;
const unsigned long RETRIEVE_TIMEOUT_MS   = 30000;
const float MEDICINE_PRESENT_CM   = 8.0;   // < this means medicine is in the tray
const int   DETECT_CONSECUTIVE    = 3;     // consecutive readings to confirm medicine drop
const unsigned long RETRIEVE_HOLD_MS = 500; // chute must stay clear this long to confirm retrieval

// ── Globals ───────────────────────────────────────────────────────────────────
const byte TRIGGER_CMD[] = {0x7E, 0x00, 0x08, 0x01, 0x00, 0x02, 0x01, 0xAB, 0xCD};
SoftwareSerial scannerSerial(SCANNER_RX, SCANNER_TX);
LiquidCrystal_I2C lcd(0x27, 16, 2);
float baselineDistance = -1;

// ── LCD ───────────────────────────────────────────────────────────────────────
void lcdPrint(const String& line1, const String& line2 = "") {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(line1.substring(0, 16));
  if (line2.length() > 0) {
    lcd.setCursor(0, 1);
    lcd.print(line2.substring(0, 16));
  }
}

// ── WiFi ──────────────────────────────────────────────────────────────────────
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) { delay(300); Serial.print("."); }
  Serial.printf("\nConnected: %s\n", WiFi.localIP().toString().c_str());
  Serial.printf(">>> Channel: %d <<<\n", wifi_get_channel());
}

// ── QR Scanner ────────────────────────────────────────────────────────────────
String tryScan() {
  Serial.print(".");
  while (scannerSerial.available()) scannerSerial.read();
  scannerSerial.write(TRIGGER_CMD, sizeof(TRIGGER_CMD));

  unsigned long waitStart = millis();
  while (!scannerSerial.available() && millis() - waitStart < SCAN_TRIGGER_WAIT_MS) {
    delay(5);
  }
  if (!scannerSerial.available()) return "";

  String data = "";
  String rawHex = "";
  unsigned long lastByte = millis();
  int  braceDepth = 0;
  bool inJson     = false;
  while (millis() - lastByte < INTER_BYTE_TIMEOUT_MS) {
    if (scannerSerial.available()) {
      char c = scannerSerial.read();
      // log every raw byte for debugging
      if (rawHex.length() < 200) {
        char buf[5];
        snprintf(buf, sizeof(buf), "%02X ", (uint8_t)c);
        rawHex += buf;
      }
      if (c >= 0x20 && c <= 0x7E) {
        data += c;
        if (c == '{') { braceDepth++; inJson = true; }
        else if (c == '}' && inJson && --braceDepth == 0) { break; }
      }
      lastByte = millis();
    }
  }
  Serial.println();
  Serial.println("[raw] " + rawHex);
  Serial.println("[str] " + data);
  if (!inJson) { Serial.println("[scan] no JSON found"); return ""; }
  int start = data.indexOf('{');
  if (start < 0) return "";
  int end = data.lastIndexOf('}');
  if (end < 0) return "";
  return data.substring(start, end + 1);
}

// ── HTTP ──────────────────────────────────────────────────────────────────────
int postScan(const String& qrJson, String& body) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected");
    return -1;
  }
  WiFiClient client;
  HTTPClient http;
  http.setTimeout(POST_TIMEOUT_MS);
  http.begin(client, SCAN_URL);
  http.addHeader("Content-Type", "application/json");
  int code = http.POST(qrJson);

  if (code > 0) {
    WiFiClient* stream = http.getStreamPtr();
    unsigned long deadline   = millis() + POST_TIMEOUT_MS;
    unsigned long captureStart = 0;  // set when "capturing" event arrives
    bool photoTaken = false;
    int  flashPhase = 0;             // 0=waiting, 1=flash1 shown, 2=flash2, 3=flash3, 4=capturing

    while (http.connected() && millis() < deadline) {

      // Timer-based LCD updates synced to the 3-flash warning sequence.
      // captureStart is set the moment the server fires the camera, so the
      // 1000 ms flash cycle on the ESP32-CAM aligns with these offsets.
      // In TEST_MODE the delays are collapsed to 200 ms each for fast iteration.
      if (captureStart > 0 && !photoTaken) {
        unsigned long elapsed = millis() - captureStart;
        unsigned long f1 = TEST_MODE ?  200 : 1000;
        unsigned long f2 = TEST_MODE ?  400 : 2000;
        unsigned long f3 = TEST_MODE ?  600 : 3100;
        if (flashPhase == 1 && elapsed >= f1) {
          lcdPrint("Flash 2 of 3", "Hold still...");
          flashPhase = 2;
        } else if (flashPhase == 2 && elapsed >= f2) {
          lcdPrint("Flash 3 of 3", "Smile!");
          flashPhase = 3;
        } else if (flashPhase == 3 && elapsed >= f3) {
          lcdPrint("Taking photo...", "Stay still!");
          flashPhase = 4;
        }
      }

      if (stream->available()) {
        String line = stream->readStringUntil('\n');
        line.trim();
        if (line.length() == 0) continue;

        if (line.indexOf("\"capturing\"") >= 0) {
          // Camera is about to start the 3-flash warning sequence
          captureStart = millis();
          flashPhase   = 1;
          lcdPrint("Flash 1 of 3", "Hold still...");
          Serial.println("Camera capturing...");
        } else if (line.indexOf("\"photo_taken\"") >= 0) {
          // Camera just did its confirmation flash
          photoTaken = true;
          lcdPrint("Photo taken!", "Verifying...");
          Serial.println("Photo taken.");
        } else if (line.indexOf("\"waiting\"") >= 0) {
          lcdPrint("Still verifying", "Please wait...");
          Serial.println("Server still processing...");
        } else {
          body = line;
          break;
        }
      }
      delay(10);
    }
  }

  http.end();
  Serial.printf("HTTP %d\n", code);
  Serial.println(body);
  return code;
}

// ── Ultrasonic ────────────────────────────────────────────────────────────────
float measureDistance() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  long duration = pulseIn(ECHO_PIN, HIGH, 30000);
  if (duration == 0) return -1;
  return duration * 0.0343f / 2.0f;
}

// ── Motors ────────────────────────────────────────────────────────────────────
void initEspNow() {
  if (esp_now_init() != 0) {
    Serial.println("ESP-NOW init failed");
    return;
  }
  esp_now_set_self_role(ESP_NOW_ROLE_CONTROLLER);
  uint8_t channel = wifi_get_channel();
  esp_now_add_peer(D1B_MAC, ESP_NOW_ROLE_SLAVE, channel, NULL, 0);
  Serial.printf("ESP-NOW peer added on WiFi channel %d\n", channel);
}

bool dispenseAndWait(int motor) {
  uint8_t cmd = (uint8_t)motor;
  esp_now_send(D1B_MAC, &cmd, 1);
  Serial.printf("Sent motor %d to D1 B\n", motor);
  lcdPrint("Dispensing...", "Motor: " + String(motor));

  // Brief pause so the mechanism has time to activate before we start polling,
  // avoiding false positives from vibration at the moment of command.
  delay(500);

  Serial.print("Waiting for medicine... ");
  unsigned long start = millis();
  int consecutive = 0;
  while (millis() - start < DISPENSE_TIMEOUT_MS) {
    float dist = measureDistance();
    // Serial.printf("[dispense] dist=%.2fcm consecutive=%d\n", dist, consecutive);
    if (dist < MEDICINE_PRESENT_CM) {
      consecutive++;
      // Require several consecutive readings within range so a brief blip
      // while the medicine is still falling is not treated as a confirmed drop.
      if (consecutive >= DETECT_CONSECUTIVE) {
        Serial.printf("detected (%.1fcm)\n", dist);
        lcdPrint("Medicine", "Detected!");
        delay(300);
        return true;
      }
    } else {
      consecutive = 0;
    }
    delay(50);
  }
  Serial.println("TIMEOUT — medicine not detected, moving on.");
  lcdPrint("Dispense error", "Call staff");
  return false;
}

// Wait for the tray sensor to return near baseline, meaning the patient has
// picked up the dispensed medicine.  Call this between consecutive medicines.
bool waitForRetrieval() {
  lcdPrint("Take medicine!", "Waiting...");
  Serial.print("Waiting for retrieval... ");

  // Step 1: confirm medicine is in the tray (3 consecutive low readings)
  unsigned long start = millis();
  int consecutive = 0;
  bool medicineInTray = false;
  while (millis() - start < RETRIEVE_TIMEOUT_MS) {
    float dist = measureDistance();
    Serial.printf("[retrieve-in] dist=%.2fcm consecutive=%d\n", dist, consecutive);
    if (dist < MEDICINE_PRESENT_CM) {
      if (++consecutive >= DETECT_CONSECUTIVE) {
        medicineInTray = true;
        break;
      }
    } else {
      consecutive = 0;
    }
    delay(50);
  }

  if (!medicineInTray) {
    Serial.println("TIMEOUT — medicine never detected in tray.");
    lcdPrint("Timeout!", "Not detected");
    return false;
  }

  // Step 2: chute must stay clear for RETRIEVE_HOLD_MS continuously.
  // A hovering hand that briefly lifts resets the timer back to zero.
  start = millis();
  unsigned long clearSince = 0;
  while (millis() - start < RETRIEVE_TIMEOUT_MS) {
    float dist = measureDistance();
    bool clear = dist > 0 && baselineDistance > 0 && dist >= baselineDistance - 2.0;
    Serial.printf("[retrieve-out] dist=%.2fcm clear=%d holdMs=%lu\n",
                  dist, clear, clearSince ? millis() - clearSince : 0UL);
    if (clear) {
      if (clearSince == 0) clearSince = millis();
      if (millis() - clearSince >= RETRIEVE_HOLD_MS) {
        Serial.printf("retrieved (%.1fcm, held %lums)\n", dist, RETRIEVE_HOLD_MS);
        lcdPrint("Got it!", "Next medicine...");
        delay(500);
        return true;
      }
    } else {
      clearSince = 0;  // hand came back — reset
    }
    delay(50);
  }

  Serial.println("TIMEOUT — medicine not retrieved, continuing.");
  lcdPrint("Timeout!", "Not retrieved");
  return false;
}

// ── Parse helpers ─────────────────────────────────────────────────────────────
String parseStringField(const String& body, const String& key) {
  int k = body.indexOf("\"" + key + "\":\"");
  if (k < 0) return "";
  int start = k + key.length() + 4;
  int end   = body.indexOf('"', start);
  if (end < 0) return "";
  return body.substring(start, end);
}

// ── Dispensed callback ────────────────────────────────────────────────────────
void postDispensed(const String& uin, const String& txHash, const String& medicinesJson) {
  if (WiFi.status() != WL_CONNECTED) return;
  String payload = "{\"uin\":\"" + uin + "\","
                   "\"transaction_hash\":\"" + txHash + "\","
                   "\"medicines\":" + medicinesJson + "}";
  WiFiClient client;
  HTTPClient http;
  http.setTimeout(POST_TIMEOUT_MS);
  http.begin(client, DISPENSED_URL);
  http.addHeader("Content-Type", "application/json");
  int code = http.POST(payload);
  Serial.printf("Dispensed callback HTTP %d\n", code);
  Serial.println(http.getString());
  http.end();
}

// ── Dispense ──────────────────────────────────────────────────────────────────
void dispenseMedicines(const String& responseBody, const String& uin) {
  if (responseBody.indexOf("\"status\":\"success\"") < 0) return;

  String txHash = parseStringField(responseBody, "transaction_hash");

  // Parse medicines array — used for both motor control and the dispensed callback
  String mKey = "\"medicines\":[";
  int mStart = responseBody.indexOf(mKey);
  if (mStart < 0) return;
  int mEnd = responseBody.indexOf(']', mStart + mKey.length());
  if (mEnd < 0) return;

  String arr = responseBody.substring(mStart + mKey.length(), mEnd);
  String medicinesJson = responseBody.substring(mStart + mKey.length() - 1, mEnd + 1);

  int pos = 0;
  while (pos < (int)arr.length()) {
    while (pos < (int)arr.length() && (arr[pos] == ',' || arr[pos] == ' ')) pos++;
    if (pos >= (int)arr.length()) break;

    int motor = arr.substring(pos).toInt();
    dispenseAndWait(motor);
    waitForRetrieval();

    while (pos < (int)arr.length() && arr[pos] != ',') pos++;
  }

  lcdPrint("All medicines", "dispensed!");
  delay(3000);

  if (txHash.length() > 0) {
    postDispensed(uin, txHash, medicinesJson);
  }
}

// ── LCD status messages ───────────────────────────────────────────────────────
void showCountdown(const String& label, int seconds) {
  for (int i = seconds; i >= 1; i--) {
    lcdPrint(label, String(i) + "...");
    delay(1000);
  }
}

void showResponseOnLcd(const String& body) {
  if (body.indexOf("\"status\":\"success\"") >= 0) {
    lcdPrint("Identity verified", "Preparing meds");
  } else if (body.indexOf("Already claimed") >= 0) {
    lcdPrint("Already claimed", "Come next month");
  } else if (body.indexOf("Face does not match") >= 0) {
    lcdPrint("Face not matched", "Please try again");
  } else if (body.indexOf("identity verification") >= 0) {
    lcdPrint("ID not verified", "Please try again");
  } else if (body.indexOf("expired_prescription") >= 0) {
    lcdPrint("Rx expired", "Get new Rx");
  } else if (body.indexOf("no valid prescription") >= 0 || body.indexOf("no_prescription") >= 0) {
    lcdPrint("No prescription", "See your doctor");
  } else if (body.indexOf("Cannot dispense") >= 0) {
    lcdPrint("Med unavailable", "Call staff");
  } else if (body.indexOf("camera_unreachable") >= 0) {
    lcdPrint("Camera offline", "Please try again");
  } else if (body.indexOf("wrong_barangay") >= 0 || body.indexOf("not in an eligible") >= 0) {
    lcdPrint("Wrong barangay", "Not eligible");
  } else if (body.indexOf("underage") >= 0) {
    lcdPrint("Not a senior", "Not eligible");
  } else {
    lcdPrint("Something went", "wrong. Try again");
  }
}

// ── Setup / Loop ──────────────────────────────────────────────────────────────
void setup() {
  Serial.begin(115200);
  scannerSerial.begin(9600);
  lcd.init();
  lcd.backlight();
  lcdPrint("VendAid", "Starting up...");

  pinMode(TRIG_PIN, OUTPUT); digitalWrite(TRIG_PIN, LOW);
  pinMode(ECHO_PIN, INPUT);

  delay(500);
  connectWiFi();
  initEspNow();

  baselineDistance = measureDistance();
  Serial.printf("Baseline distance: %.1fcm\n", baselineDistance);
  Serial.println("--- Scanner ready ---");
  lcdPrint("Ready", "Scan your QR");
}

void loop() {
  // Heartbeat: cycle dots on second line so user knows the system is alive
  static int   dotCount   = 1;
  static unsigned long lastDot = 0;
  if (millis() - lastDot >= 600) {
    String dots = "";
    for (int i = 0; i < dotCount; i++) dots += ".";
    lcdPrint("Scan your QR", dots);
    dotCount = (dotCount % 3) + 1;
    lastDot  = millis();
  }

  String qr = tryScan();
  if (qr.length() == 0) return;

  Serial.println();
  Serial.println("Scanned: " + qr);
  lcdPrint("QR detected!", "");
  delay(500);

  // Show before sending POST; LCD stays here until "capturing" event arrives
  lcdPrint("Look at camera!", "Get ready...");
  if (TEST_MODE) delay(1000);
  Serial.println("[sending to server...]");

  String responseBody;
  int code = postScan(qr, responseBody);

  if (code > 0) {
    showResponseOnLcd(responseBody);
    delay(2000);
    String uin = parseStringField(qr, "uin");
    dispenseMedicines(responseBody, uin);
  } else {
    lcdPrint("No response", "Please try again");
    delay(2000);
  }

  Serial.printf("[cooling down %lums]\n", COOLDOWN_MS);
  lcdPrint("VendAid", "Starting up...");
  delay(COOLDOWN_MS);
}
