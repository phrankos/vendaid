// ═════════════════════════════════════════════════════════════════════════════
// Arduino Mini — sample motor handler
//
// Receives motor numbers from D1 B via hardware serial (pin 0 RX).
// Runs the matching motor for MOTOR_RUN_MS milliseconds then stops it.
//
// Wiring:
//   D1 B pin D7 (TX) → Arduino pin 0 (RX)
//   GND → GND (shared)
//   Motor 0 → pin 2
//   Motor 1 → pin 3
//   Motor 2 → pin 4
//   Motor 3 → pin 5
//
// NOTE: Disconnect pin 0 (RX) before uploading, reconnect after.
// ═════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// COMMUNICATION LAYER — do not modify
// ─────────────────────────────────────────────────────────────────────────────

void handleMotor(int motor);

void setup() {
  Serial.begin(9600);
  Serial.println("Arduino ready — waiting for motor commands from D1 B.");
  motorSetup();
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

#define MOTOR_0  2
#define MOTOR_1  3
#define MOTOR_2  4
#define MOTOR_3  5

const unsigned long MOTOR_RUN_MS = 2000;  // how long each motor runs

void motorSetup() {
  pinMode(MOTOR_0, OUTPUT); digitalWrite(MOTOR_0, LOW);
  pinMode(MOTOR_1, OUTPUT); digitalWrite(MOTOR_1, LOW);
  pinMode(MOTOR_2, OUTPUT); digitalWrite(MOTOR_2, LOW);
  pinMode(MOTOR_3, OUTPUT); digitalWrite(MOTOR_3, LOW);
}

void runMotor(int pin) {
  digitalWrite(pin, HIGH);
  delay(MOTOR_RUN_MS);
  digitalWrite(pin, LOW);
}

void handleMotor(int motor) {
  switch (motor) {
    case 0:
      Serial.println("Running motor 0");
      runMotor(MOTOR_0);
      break;
    case 1:
      Serial.println("Running motor 1");
      runMotor(MOTOR_1);
      break;
    case 2:
      Serial.println("Running motor 2");
      runMotor(MOTOR_2);
      break;
    case 3:
      Serial.println("Running motor 3");
      runMotor(MOTOR_3);
      break;
    default:
      Serial.print("Motor ");
      Serial.print(motor);
      Serial.println(" not handled by this board.");
      break;
  }
}
