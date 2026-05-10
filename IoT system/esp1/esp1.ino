#include <AViShaMQTT.h>

const char *ssid = "hmmmmm";
const char *password = "satusampelimaa";
const char* mqtt_server = "broker.emqx.io";

AViShaMQTT mqtt(ssid, password, mqtt_server);

const char *topicIR1 = "infra/1";
const char *topicIR2 = "infra/2";
const char *topicIR3 = "infra/3";

unsigned long timer = 0;
unsigned long intervalKirim = 1000;

#define IR1 16
#define IR2 17
#define IR3 13

void setup() {
  Serial.begin(115200);

  pinMode(IR1, INPUT);
  pinMode(IR2, INPUT);
  pinMode(IR3, INPUT);

  mqtt.begin();
}

void loop() {
  mqtt.loop();

  int sensor1 = digitalRead(IR1);
  int sensor2 = digitalRead(IR2);
  int sensor3 = digitalRead(IR3);

  Serial.print("IR1: ");
  Serial.print(sensor1);
  Serial.print(" | IR2: ");
  Serial.print(sensor2);
  Serial.print(" | IR3: ");
  Serial.println(sensor3);

  if (millis() - timer >= intervalKirim) {
    timer = millis();

    mqtt.publish(topicIR1, String(sensor1));
    mqtt.publish(topicIR2, String(sensor2));
    mqtt.publish(topicIR3, String(sensor3));
  }
}