#include <Stepper.h>

int stepsPerRevolution = 2048;
int rpm = 15;

Stepper motor1(stepsPerRevolution, 2, 4, 3, 5);
Stepper motor2(stepsPerRevolution, 6, 8, 7, 9);
Stepper motor3(stepsPerRevolution, 10, 12, 11, 13);
Stepper motor4(stepsPerRevolution, A0, A2, A1, A3);

void setup() {
  Serial.begin(9600);

  motor1.setSpeed(rpm);
  motor2.setSpeed(rpm);
  motor3.setSpeed(rpm);
  motor4.setSpeed(rpm);
}

void loop() {
  if (Serial.available() > 0) {
    char cmd = Serial.read();

    // ignore newline chars
    if (cmd == '\n' || cmd == '\r') return;

    if (cmd == '1') {
      motor1.step(-stepsPerRevolution);
    }
    else if (cmd == '2') {
      motor2.step(-stepsPerRevolution);
    }
    else if (cmd == '3') {
      motor3.step(-stepsPerRevolution);
    }
    else if (cmd == '4') {
      motor4.step(-stepsPerRevolution);
    }
  }
}