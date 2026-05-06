// ═════════════════════════════════════════════════════════════════════════════
// D1 Motor Controller — template
//
// HOW TO USE THIS FILE
// ────────────────────
// 1. Scroll down to the section marked "YOUR CODE HERE".
// 2. Wire your motors and define their pins there.
// 3. Fill in the handleMotor() function — it is called automatically whenever
//    D1 A sends a motor command. The motor number (0–7) is passed as the
//    argument; just run whichever motor matches.
// 4. Do NOT touch anything outside that section.
//
// Wiring (to Arduino):
//   D1 pin D7 (TX) → Arduino pin 0 (RX)
//   GND → GND (shared)
// ═════════════════════════════════════════════════════════════════════════════

#include <ESP8266WiFi.h>
#include <espnow.h>
#include <SoftwareSerial.h>

// ─────────────────────────────────────────────────────────────────────────────
// COMMUNICATION LAYER — do not modify
// ─────────────────────────────────────────────────────────────────────────────

const char* WIFI_SSID = "cvmigwifi";
const char* WIFI_PASS = "v1s1on-trans4m3r";

// D7 = TX to Arduino, D2 = dummy RX (unused)
SoftwareSerial toArduino(D2, D7);

void handleMotor(int motor);  // forward declaration — defined below

void onReceive(uint8_t* mac, uint8_t* data, uint8_t len) {
  if (len < 1) return;
  int motor = data[0];
  Serial.printf("[ESP-NOW] received motor: %d\n", motor);
  toArduino.write((uint8_t)motor);
  Serial.printf("Forwarded motor %d to Arduino\n", motor);
  handleMotor(motor);
}

void setup() {
  Serial.begin(115200);
  toArduino.begin(9600);
  delay(2000);

  Serial.print("MAC: ");
  Serial.println(WiFi.macAddress());

  // Connect to WiFi so this board locks onto the same channel as D1 A.
  // ESP-NOW requires both sender and receiver to be on the same channel.
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) { delay(300); Serial.print("."); }
  Serial.printf("\nConnected: %s\n", WiFi.localIP().toString().c_str());
  Serial.printf(">>> Channel: %d <<<\n", wifi_get_channel());

  if (esp_now_init() != 0) {
    Serial.println("ESP-NOW init failed — halting");
    while (true) delay(1000);
  }
  esp_now_set_self_role(ESP_NOW_ROLE_SLAVE);
  esp_now_register_recv_cb(onReceive);

  Serial.println("Ready — waiting for motor commands from D1 A.");

  motorSetup();  // calls your pin setup below
}

void loop() {
  static unsigned long lastHeartbeat = 0;
  if (millis() - lastHeartbeat >= 5000) {
    Serial.println("Alive — waiting for motor commands.");
    lastHeartbeat = millis();
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// YOUR CODE HERE
// ─────────────────────────────────────────────────────────────────────────────

// Define your motor pins here
// #define MOTOR_0  D5
// #define MOTOR_1  D6
// #define MOTOR_2  D3
// #define MOTOR_3  D4

// Called once at startup — set your pin modes here
void motorSetup() {
  // pinMode(MOTOR_0, OUTPUT);
  // pinMode(MOTOR_1, OUTPUT);
  // pinMode(MOTOR_2, OUTPUT);
  // pinMode(MOTOR_3, OUTPUT);
}

// Called automatically every time D1 A sends a motor command.
// motor = the motor number (0–7). Run whichever motor matches.
void handleMotor(int motor) {
  switch (motor) {
    // case 0: digitalWrite(MOTOR_0, HIGH); break;
    // case 1: digitalWrite(MOTOR_1, HIGH); break;
    // case 2: digitalWrite(MOTOR_2, HIGH); break;
    // case 3: digitalWrite(MOTOR_3, HIGH); break;
    default:
      Serial.printf("Motor %d not handled by this board.\n", motor);
      break;
  }
}
