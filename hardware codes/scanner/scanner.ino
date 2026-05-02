
// Wiring:
//   GM861S TXD (pin 5) -> D5 (WeMos)
//   GM861S RXD (pin 4) -> D6 (WeMos)
//   GM861S 5V          -> VIN, GND -> GND

#include <SoftwareSerial.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

#define SCANNER_RX D5
#define SCANNER_TX D6

const char* WIFI_SSID = "Converge_2.4GHz_fG6m";
const char* WIFI_PASS = "eQZxC6sD";
const char* SCAN_URL  = "http://192.168.100.246:8000/api/scan";


const unsigned long POST_TIMEOUT_MS = 30000;

// How long to wait for a barcode after triggering
const unsigned long SCAN_REPLY_WAIT_MS = 4000;

// Inter-byte timeout that marks the end of a scanner reply.
const unsigned long INTER_BYTE_TIMEOUT_MS = 300;

// After the server responds, pause this long before scanning again.
const unsigned long COOLDOWN_MS = 5000;

// GM861S Command-Triggered
const byte TRIGGER_CMD[] = {0x7E, 0x00, 0x08, 0x01, 0x00, 0x02, 0x01, 0xAB, 0xCD};

SoftwareSerial scanner(SCANNER_RX, SCANNER_TX);

void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) { delay(300); Serial.print("."); }
  Serial.printf("\nConnected: %s\n", WiFi.localIP().toString().c_str());
}

// Trigger one scan and return the resulting string, or "" if nothing decoded.
String tryScan() {
  Serial.print(".");                          
  while (scanner.available()) scanner.read();   // flush stale bytes
  scanner.write(TRIGGER_CMD, sizeof(TRIGGER_CMD));

  unsigned long waitStart = millis();
  while (!scanner.available() && millis() - waitStart < SCAN_REPLY_WAIT_MS) {
    delay(5);
  }
  if (!scanner.available()) return "";

  String data = "";
  unsigned long lastByte = millis();
  unsigned long overallStart = millis();
  while (millis() - lastByte < INTER_BYTE_TIMEOUT_MS &&
         millis() - overallStart < SCAN_REPLY_WAIT_MS) {
    if (scanner.available()) {
      char c = scanner.read();
      if (c >= 0x20 && c <= 0x7E) data += c;
      lastByte = millis();
    }
  }
  // Drop ACK / fragment replies that aren't the QR JSON.
  int start = data.indexOf('{');
  int end = data.lastIndexOf('}');
  if (start < 0 || end < 0 || end < start) return "";
  return data.substring(start, end + 1);
}

// Returns the HTTP code from Laravel 
// -1  network/transport failure - keep trying.
int postScan(const String& qrJson) {
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
  String resp = http.getString();
  http.end();
  Serial.printf("HTTP %d\n", code);
  Serial.println(resp);
  return code;
}

void setup() {
  Serial.begin(115200);
  scanner.begin(9600);
  delay(500);
  connectWiFi();
  Serial.println("--- Scanner running. Continuously scanning. ---");
}

void loop() {
  String qr = tryScan();
  if (qr.length() == 0) return;     // no QR seen this cycle, try again

  Serial.println();                 
  Serial.println("Scanned: " + qr);
  int code = postScan(qr);

  if (code > 0) {
    // Server answered. Cool down before scanning again so the same QR isn't POSTed repeatedly
    Serial.printf("[server responded; cooling down %lums]\n", COOLDOWN_MS);
    delay(COOLDOWN_MS);
  }
}
