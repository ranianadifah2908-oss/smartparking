#include <AViShaMQTT.h>

const char *ssid = "hmmmmm";
const char *password = "satusampelimaa";
const char* mqtt_server = "broker.emqx.io";

AViShaMQTT mqtt(ssid, password, mqtt_server);

const char *topicUltrasonic = "esp2/ultrasonic";
const char *topicFlame      = "esp2/flame";

unsigned long timer = 0;
unsigned long intervalKirim = 1000;

#define TRIG 5
#define ECHO 18
#define FLAME_PIN 13

long durasi;
float jarak;

void setup() {
  Serial.begin(115200);

  pinMode(TRIG, OUTPUT);
  pinMode(ECHO, INPUT);
  pinMode(FLAME_PIN, INPUT);

  mqtt.begin();

  Serial.println("ESP2 Ready...");
}

void loop() {

  mqtt.loop();

  if (millis() - timer >= intervalKirim) {

    timer = millis();

    digitalWrite(TRIG, LOW);
    delayMicroseconds(2);

    digitalWrite(TRIG, HIGH);
    delayMicroseconds(10);

    digitalWrite(TRIG, LOW);

    durasi = pulseIn(ECHO, HIGH);

    jarak = durasi * 0.034 / 2;

    if (jarak <= 0 || jarak > 400) {
      jarak = 0;
    }

    int flame = digitalRead(FLAME_PIN);

    String statusFlame;

    if (flame == LOW) {
      statusFlame = "API";
    } else {
      statusFlame = "AMAN";
    }

    Serial.print("Jarak : ");
    Serial.print(jarak);
    Serial.println(" cm");

    Serial.print("Flame : ");
    Serial.println(statusFlame);

    Serial.println("----------------------");

    mqtt.publish(topicUltrasonic, String(jarak));
    mqtt.publish(topicFlame, statusFlame);
  }
}