#include <Stepper.h>

int stepsPerRevolution = 2048;
int rpm = 15;

Stepper motor5(stepsPerRevolution, 2, 4, 3, 5);
Stepper motor6(stepsPerRevolution, 6, 8, 7, 9);
Stepper motor7(stepsPerRevolution, 10, 12, 11, 13);
Stepper motor8(stepsPerRevolution, A0, A2, A1, A3);

void setup() {
  Serial.begin(9600);

  motor5.setSpeed(rpm);
  motor6.setSpeed(rpm);
  motor7.setSpeed(rpm);
  motor8.setSpeed(rpm);
}

void loop() {
  if (Serial.available() > 0) {
    char cmd = Serial.read();

    if (cmd == '\n' || cmd == '\r') return;

    if (cmd == '5') {
      motor5.step(-stepsPerRevolution);
    }
    else if (cmd == '6') {
      motor6.step(-stepsPerRevolution);
    }
    else if (cmd == '7') {
      motor7.step(-stepsPerRevolution);
    }
    else if (cmd == '8') {
      motor8.step(-stepsPerRevolution);
    }
  }
}