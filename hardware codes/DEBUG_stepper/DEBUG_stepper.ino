#include <Stepper.h>

// Steps per revolution (28BYJ-48 = 2048)
int stepsPerRevolution = 2048;

// Speed in RPM
int rpm = 15;

// Define 4 motors
Stepper motor1(stepsPerRevolution, 2, 4, 3, 5);
Stepper motor2(stepsPerRevolution, 6, 8, 7, 9);
Stepper motor3(stepsPerRevolution, 10, 12, 11, 13);
Stepper motor4(stepsPerRevolution, A0, A2, A1, A3);

void setup() {
  motor1.setSpeed(rpm);
  motor2.setSpeed(rpm);
  motor3.setSpeed(rpm);
  motor4.setSpeed(rpm);

  Serial.begin(9600);
  Serial.println("Commands: 1-4 = rotate motor once");
}

void loop() {
  if (Serial.available() > 0) {
    char command = Serial.read();

    if (command == '\n' || command == '\r') return;

    if (command == '1') {
      Serial.println("Motor 1 rotating 1 revolution...");
      motor1.step(-stepsPerRevolution);
      Serial.println("Done.");
    } 
    else if (command == '2') {
      Serial.println("Motor 2 rotating 1 revolution...");
      motor2.step(-stepsPerRevolution);
      Serial.println("Done.");
    } 
    else if (command == '3') {
      Serial.println("Motor 3 rotating 1 revolution...");
      motor3.step(-stepsPerRevolution);
      Serial.println("Done.");
    } 
    else if (command == '4') {
      Serial.println("Motor 4 rotating 1 revolution...");
      motor4.step(-stepsPerRevolution);
      Serial.println("Done.");
    } 
    else {
      Serial.println("Unknown command. Use 1-4.");
    }
  }
}