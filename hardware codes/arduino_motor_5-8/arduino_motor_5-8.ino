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

#include <Stepper.h>
int stepsPerRevolution = 2048;
int rpm = 15;

Stepper motor5(stepsPerRevolution, 2, 4, 3, 5);
Stepper motor6(stepsPerRevolution, 6, 8, 7, 9);
Stepper motor7(stepsPerRevolution, 10, 12, 11, 13);
Stepper motor8(stepsPerRevolution, A0, A2, A1, A3);

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

// Release coils after stepping to stop current draw and LED drain
void releaseMotor(int p1, int p2, int p3, int p4) {
  digitalWrite(p1, LOW);
  digitalWrite(p2, LOW);
  digitalWrite(p3, LOW);
  digitalWrite(p4, LOW);
}

// Called once at startup — set your pin modes here
void motorSetup() {
  motor5.setSpeed(rpm);
  motor6.setSpeed(rpm);
  motor7.setSpeed(rpm);
  motor8.setSpeed(rpm);
}

// Called automatically every time D1 B forwards a motor command.
// motor = the motor number (0–7). Run whichever motor matches.
void handleMotor(int motor) {
  switch (motor) {
    case 5:
      motor5.step(-stepsPerRevolution);
      releaseMotor(2, 4, 3, 5);
      break;
    case 6:
      motor6.step(-stepsPerRevolution);
      releaseMotor(6, 8, 7, 9);
      break;
    case 7:
      motor7.step(-stepsPerRevolution);
      releaseMotor(10, 12, 11, 13);
      break;
    case 8:
      motor8.step(-stepsPerRevolution);
      releaseMotor(A0, A2, A1, A3);
      break;
    default:
      Serial.print("Motor ");
      Serial.print(motor);
      Serial.println(" not handled by this board.");
      break;
  }
}
