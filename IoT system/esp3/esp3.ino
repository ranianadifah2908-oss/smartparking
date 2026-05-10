#include <WiFi.h>
#include <PubSubClient.h>
#include <ESP32Servo.h>

const char* ssid = "hmmmmm";
const char* password = "satusampelimaa";

const char* mqtt_server = "broker.emqx.io";

WiFiClient espClient;
PubSubClient client(espClient);

const char* topicUltrasonic = "esp2/ultrasonic";
const char* topicFlame      = "esp2/flame";

const char* topicServo2     = "servo2/control";

#define SERVO1_PIN 26
#define SERVO2_PIN 27
#define BUZZER_PIN 4

Servo servo1;
Servo servo2;

float jarak = 0;
String flameStatus = "AMAN";

void setup() {

  Serial.begin(115200);

  servo1.attach(SERVO1_PIN);
  servo2.attach(SERVO2_PIN);

  servo1.write(0);
  servo2.write(0);

  pinMode(BUZZER_PIN, OUTPUT);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi Connected");

  client.setServer(mqtt_server, 1883);
  client.setCallback(callback);
  client.subscribe(topicUltrasonic);
  client.subscribe(topicFlame);
  client.subscribe(topicServo2);


  reconnect();
}

void loop() {

  if (!client.connected()) {
    reconnect();
  }

  client.loop();

  if (jarak < 5 && jarak > 0) {
    servo1.write(90);
  } 
  else {
    servo1.write(0);
  }

  if (flameStatus == "API") {
    digitalWrite(BUZZER_PIN, LOW);
  }
  else {
    digitalWrite(BUZZER_PIN, HIGH);
  }
}

void callback(char* topic, byte* payload, unsigned int length) {

  String message = "";

  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }

  Serial.print("Topic: ");
  Serial.println(topic);

  Serial.print("Message: ");
  Serial.println(message);

  if (String(topic) == topicUltrasonic) {

    jarak = message.toFloat();

    Serial.print("Jarak diterima: ");
    Serial.println(jarak);
  }

  if (String(topic) == topicFlame) {

    flameStatus = message;

    Serial.print("Flame diterima: ");
    Serial.println(flameStatus);
  }

  if (String(topic) == topicServo2) {

    if (message == "ON") {
      servo2.write(90);
    } 
    else {
      servo2.write(0);
    }
  }
}

void reconnect() {

  while (!client.connected()) {

    Serial.print("Connecting MQTT...");

    if (client.connect("ESP32_CONTROL")) {

      Serial.println("Connected");

      client.subscribe(topicUltrasonic);
      client.subscribe(topicFlame);
      client.subscribe(topicServo2);

    } 
    else {

      Serial.print("Failed, rc=");
      Serial.print(client.state());

      delay(2000);
    }
  }
}