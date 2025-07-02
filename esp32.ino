#include <WiFi.h>
#include <HTTPClient.h>
#include <HardwareSerial.h>

// WiFi credentials
// TODO: Change these example values to your actual WiFi credentials
const char *ssid = "YOUR_WIFI_SSID";         // e.g., "MyWiFi"
const char *password = "YOUR_WIFI_PASSWORD"; // e.g., "password123"

// Server details
// TODO: Change this to your actual server URL
const char *serverUrl = "http://your-server-ip/attendance_system/api/api.php"; // e.g., "http://192.168.1.100/attendance_system/api/api.php"

// Hardware Serial for communication with Arduino
HardwareSerial arduinoSerial(2); // Using UART2 (GPIO16-RX, GPIO17-TX)

void setup()
{
  Serial.begin(115200);                            // For debugging
  arduinoSerial.begin(115200, SERIAL_8N1, 16, 17); // RX, TX

  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nConnected with IP: ");
  Serial.println(WiFi.localIP());
}

void loop()
{
  // Check for messages from Arduino
  if (arduinoSerial.available())
  {
    String message = arduinoSerial.readStringUntil('\n');
    message.trim();

    if (message.startsWith("!{\"fid\":"))
    {
      // Extract fingerprint ID
      int fidStart = message.indexOf(":") + 1;
      int fidEnd = message.indexOf("}");
      String fidStr = message.substring(fidStart, fidEnd);
      int fingerprintID = fidStr.toInt();

      Serial.print("Received fingerprint ID: ");
      Serial.println(fingerprintID);

      // Send to server
      sendToServer(fingerprintID);
    }
  }

  // Small delay to prevent flooding
  delay(100);
}

void sendToServer(int fingerprintID)
{
  if (WiFi.status() == WL_CONNECTED)
  {
    HTTPClient http;

    // Create request URL
    String url = String(serverUrl) + "?action=record_attendance&fid=" + String(fingerprintID);

    Serial.print("Sending request to: ");
    Serial.println(url);

    http.begin(url);
    int httpCode = http.GET();

    if (httpCode > 0)
    {
      String payload = http.getString();
      Serial.print("Server response: ");
      Serial.println(payload);

      // Send response back to Arduino
      arduinoSerial.println(payload);
    }
    else
    {
      Serial.print("Error on HTTP request: ");
      Serial.println(httpCode);
      arduinoSerial.println("{\"error\":\"HTTP error " + String(httpCode) + "\"}");
    }
    http.end();
  }
  else
  {
    Serial.println("WiFi not connected");
    arduinoSerial.println("{\"error\":\"WiFi not connected\"}");
  }
}