#include <Arduino.h>
#include "esp_camera.h"
#include "FS.h"
#include "SD_MMC.h"
#include <WiFi.h>
#include <WebServer.h>
#include <base64.h>

#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27
#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5
#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22
//#define FLASH_LED_PIN      4

// const char *ssid     = "cvmigwifi";
// const char *password = "v1s1on-trans4m3r";
const char *ssid     ="Converge_2.4GHz_fG6m";
const char *password = "eQZxC6sD";

WebServer server(80);
int photoCount = 0;

// Store last photo in memory so /latest can serve it
uint8_t *lastPhoto    = NULL;
size_t   lastPhotoLen = 0;

bool savePhoto() {
  //digitalWrite(FLASH_LED_PIN, HIGH);
  //delay(200);
  // Discard first frame so exposure adjusts
  camera_fb_t *fb = esp_camera_fb_get();
  esp_camera_fb_return(fb);
  // Actual capture
  fb = esp_camera_fb_get();
  //digitalWrite(FLASH_LED_PIN, LOW);
  if (!fb) {
    Serial.println("Camera capture failed");
    return false;
  }

  uint8_t *jpg_buf = NULL;
  size_t jpg_len = 0;
  bool converted = fmt2jpg(
    fb->buf, fb->len,
    fb->width, fb->height,
    PIXFORMAT_GRAYSCALE,
    70,
    &jpg_buf, &jpg_len
  );
  esp_camera_fb_return(fb);

  if (!converted || jpg_len == 0) {
    Serial.println("JPEG conversion failed");
    free(jpg_buf);
    return false;
  }

  // Save to SD card
  char filename[32];
  snprintf(filename, sizeof(filename), "/photo_%03d.jpg", photoCount++);
  File file = SD_MMC.open(filename, FILE_WRITE);
  if (!file) {
    Serial.printf("Failed to open file: %s\n", filename);
    free(jpg_buf);
    return false;
  }
  file.write(jpg_buf, jpg_len);
  file.close();
  Serial.printf("Saved %s (%d bytes)\n", filename, jpg_len);
  Serial.printf("View at: http://%s/latest\n", WiFi.localIP().toString().c_str()); // ← add this

  if (lastPhoto != NULL) free(lastPhoto);
  lastPhoto    = jpg_buf;
  lastPhotoLen = jpg_len;

  return true;
}

void handleCapture() {
  Serial.println("Capture request received from ESP8266!");
  bool ok = savePhoto();
  if (ok) {
    server.send(200, "text/plain", "OK - Photo saved");
  } else {
    server.send(500, "text/plain", "FAILED - Photo not saved");
  }
}

// Same as /capture but returns the JPEG as base64 in the response body.
// Streams the encoding in chunks so we never allocate one giant String --
// works at UXGA where the full base64 would be ~200KB.
void handleCaptureB64() {
  Serial.println("capture_b64 request from D1");
  if (!savePhoto() || lastPhoto == NULL || lastPhotoLen == 0) {
    server.send(500, "text/plain", "FAILED");
    return;
  }

  server.sendHeader("Cache-Control", "no-store");
  server.setContentLength(CONTENT_LENGTH_UNKNOWN);   // chunked transfer
  server.send(200, "text/plain", "");

  // Encode 3KB of input -> 4KB of base64 output per chunk.
  const size_t IN_CHUNK = 3072;
  size_t off = 0;
  while (off < lastPhotoLen) {
    size_t n = (lastPhotoLen - off > IN_CHUNK) ? IN_CHUNK : (lastPhotoLen - off);
    String piece = base64::encode(lastPhoto + off, n);
    server.sendContent(piece);
    off += n;
  }
  server.sendContent("");   // terminate chunked response
}

void handleLatest() {
  if (lastPhoto == NULL || lastPhotoLen == 0) {
    server.send(404, "text/plain", "No photo taken yet. Trigger a capture first.");
    return;
  }
  server.sendHeader("Cache-Control", "no-cache");
  server.send_P(200, "image/jpeg", (const char *)lastPhoto, lastPhotoLen);
}

void handleRoot() {
  String html = "<!DOCTYPE html><html><head><title>ESP32-CAM</title>";
  html += "<style>body{background:#111;color:#fff;font-family:sans-serif;padding:20px;}";
  html += "a{color:#4fc3f7;display:block;margin:8px 0;text-decoration:none;}";
  html += "a:hover{color:#fff;} .del{color:#ef5350;margin-left:10px;}";
  html += "img{max-width:100%;margin-top:20px;border:2px solid #444;}";
  html += "button{padding:10px 20px;background:#2196F3;color:white;border:none;border-radius:6px;cursor:pointer;font-size:15px;}";
  html += "button:hover{background:#1976D2;}</style></head><body>";
  html += "<h2>ESP32-CAM</h2>";
  html += "<button onclick=\"fetch('/capture').then(r=>r.text()).then(t=>{alert(t);document.getElementById('latest').src='/latest?t='+Date.now();})\">Take Photo</button>";
  html += "<br><img id='latest' src='/latest' onerror=\"this.style.display='none'\" />";
  html += "<h3>SD Card Photos</h3>";

  File root = SD_MMC.open("/");
  File file = root.openNextFile();
  int count = 0;
  while (file) {
    String name = file.name();
    if (name.endsWith(".jpg") || name.endsWith(".JPG")) {
      html += "<div><a href='/photo?name=" + name + "' target='_blank'>" + name + " (" + String(file.size()) + " bytes)</a>";
      html += "<a class='del' href='/delete?name=" + name + "' onclick=\"return confirm('Delete " + name + "?')\">delete</a></div>";
      count++;
    }
    file = root.openNextFile();
  }
  if (count == 0) html += "<p>No photos on SD card yet.</p>";
  html += "<p>" + String(count) + " photo(s) found.</p>";
  html += "</body></html>";
  server.send(200, "text/html", html);
}

void handlePhoto() {
  if (!server.hasArg("name")) {
    server.send(400, "text/plain", "Missing name parameter");
    return;
  }
  String filename = "/" + server.arg("name");
  File file = SD_MMC.open(filename, FILE_READ);
  if (!file) {
    server.send(404, "text/plain", "File not found");
    return;
  }
  server.streamFile(file, "image/jpeg");
  file.close();
}

void handleDelete() {
  if (!server.hasArg("name")) {
    server.send(400, "text/plain", "Missing name parameter");
    return;
  }
  String filename = "/" + server.arg("name");
  if (SD_MMC.remove(filename)) {
    server.send(200, "text/plain", "Deleted " + filename);
    Serial.println("Deleted: " + filename);
  } else {
    server.send(500, "text/plain", "Failed to delete " + filename);
  }
}

void setup() {
  Serial.begin(115200);
  Serial.setDebugOutput(true);
  Serial.println();
  //pinMode(FLASH_LED_PIN, OUTPUT);
  //digitalWrite(FLASH_LED_PIN, LOW);

  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer   = LEDC_TIMER_0;
  config.pin_d0       = Y2_GPIO_NUM;
  config.pin_d1       = Y3_GPIO_NUM;
  config.pin_d2       = Y4_GPIO_NUM;
  config.pin_d3       = Y5_GPIO_NUM;
  config.pin_d4       = Y6_GPIO_NUM;
  config.pin_d5       = Y7_GPIO_NUM;
  config.pin_d6       = Y8_GPIO_NUM;
  config.pin_d7       = Y9_GPIO_NUM;
  config.pin_xclk     = XCLK_GPIO_NUM;
  config.pin_pclk     = PCLK_GPIO_NUM;
  config.pin_vsync    = VSYNC_GPIO_NUM;
  config.pin_href     = HREF_GPIO_NUM;
  config.pin_sccb_sda = SIOD_GPIO_NUM;
  config.pin_sccb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn     = PWDN_GPIO_NUM;
  config.pin_reset    = RESET_GPIO_NUM;

  config.xclk_freq_hz = 10000000;
  config.pixel_format = PIXFORMAT_GRAYSCALE;
  config.frame_size   = FRAMESIZE_SVGA;
  config.grab_mode    = CAMERA_GRAB_WHEN_EMPTY;
  config.fb_location  = CAMERA_FB_IN_PSRAM;
  config.jpeg_quality = 12;
  config.fb_count     = 1;

  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed: 0x%x\n", err);
    return;
  }
  Serial.println("Camera init OK");

  sensor_t *s = esp_camera_sensor_get();
  if (s->id.PID == OV3660_PID) {
    s->set_vflip(s, 1);
    s->set_brightness(s, 0);
    s->set_saturation(s, -2);
    s->set_special_effect(s, 0);
    s->set_exposure_ctrl(s, 1);
    s->set_aec2(s, 1);
    s->set_gain_ctrl(s, 1);
    s->set_sharpness(s, 2);
    s->set_denoise(s, 1);
    s->set_contrast(s, 1);
  }

  if (!SD_MMC.begin("/sdcard", true)) {
    Serial.println("SD card mount failed!");
    return;
  }
  if (SD_MMC.cardType() == CARD_NONE) {
    Serial.println("No SD card found!");
    return;
  }
  Serial.printf("SD card OK - %llu MB\n", SD_MMC.cardSize() / (1024 * 1024));

  // Connect to WiFi
  WiFi.begin(ssid, password);
  WiFi.setSleep(false);
  Serial.print("WiFi connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");
  Serial.print("ESP32-CAM IP: ");
  Serial.println(WiFi.localIP());

  // Start web server
  server.on("/", handleRoot);
  server.on("/capture", handleCapture);
  server.on("/capture_b64", handleCaptureB64);
  server.on("/latest", handleLatest);
  server.on("/photo", handlePhoto);
  server.on("/delete", handleDelete);
  server.begin();

  Serial.println("Ready! Waiting for capture requests from ESP8266...");
  Serial.println("Or open http://<IP>/ in browser to view photos.");
  Serial.println("Or type '1' in Serial Monitor to capture manually.");
}

void loop() {
  server.handleClient();

  if (Serial.available() > 0) {
    String input = Serial.readStringUntil('\n');
    input.trim();
    if (input == "1") {
      Serial.println("Taking photo...");
      savePhoto();
    } else {
      Serial.println("Unknown command. Type '1' to take a photo.");
    }
  }
}