// ═════════════════════════════════════════════════════════════════════════════
// Arduino Motor Controller — template
//
// HOW TO USE THIS FILE
// ────────────────────
// 1. Scroll down to the section marked "YOUR CODE HERE".
// 2. Wire your motors and define their pins there.
// 3. Fill in the handleMotor() function — it is called automatically whenever
//    D1 B forwards a motor command. The motor number is passed as the argument;
//    just run whichever motor matches.
// 4. Do NOT touch anything outside that section.
//
// Wiring (from D1 B):
//   D1 B pin D7 (TX) → Arduino pin 0 (RX)
//   GND → GND (shared)
//
// NOTE: Disconnect the wire from Arduino pin 0 before uploading,
//       then reconnect it after the upload is done.
// ═════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// COMMUNICATION LAYER — do not modify
// ─────────────────────────────────────────────────────────────────────────────

void handleMotor(int motor);  // forward declaration — defined below

void setup() {
  Serial.begin(9600);
  Serial.println("Arduino ready — waiting for motor commands from D1 B.");
  motorSetup();  // calls your pin setup below
}

void loop() {
  if (Serial.available()) {
    int motor = Serial.read();
    Serial.print("[Serial] received motor: ");
    Serial.println(motor);
    handleMotor(motor);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// YOUR CODE HERE
// ─────────────────────────────────────────────────────────────────────────────

// Define your motor pins here
// #define MOTOR_4  2
// #define MOTOR_5  3
// #define MOTOR_6  4
// #define MOTOR_7  5

// Called once at startup — set your pin modes here
void motorSetup() {
  // pinMode(MOTOR_4, OUTPUT);
  // pinMode(MOTOR_5, OUTPUT);
  // pinMode(MOTOR_6, OUTPUT);
  // pinMode(MOTOR_7, OUTPUT);
}

// Called automatically every time D1 B forwards a motor command.
// motor = the motor number (0–7). Run whichever motor matches.
void handleMotor(int motor) {
  switch (motor) {
    // case 4: digitalWrite(MOTOR_4, HIGH); break;
    // case 5: digitalWrite(MOTOR_5, HIGH); break;
    // case 6: digitalWrite(MOTOR_6, HIGH); break;
    // case 7: digitalWrite(MOTOR_7, HIGH); break;
    default:
      Serial.print("Motor ");
      Serial.print(motor);
      Serial.println(" not handled by this board.");
      break;
  }
}
