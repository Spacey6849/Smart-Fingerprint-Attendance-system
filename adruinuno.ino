#include <SoftwareSerial.h>
#include <Adafruit_Fingerprint.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// LCD Setup (16x2)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Fingerprint Sensor Setup
SoftwareSerial fingerSerial(2, 3); // RX, TX
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&fingerSerial);

// Buzzer Pin
const int buzzerPin = 7;

// Message Templates
const char* welcomeMsg = " Scan Fingerprint ";
const char* readyMsg = "  Ready to Scan  ";
const char* successMsg = "Attendance Saved!";
const char* errorMsg = " Invalid Scan! ";
const char* notRegisteredMsg = "Fingerprint not";
const char* registeredMsg = " registered! ";
const char* enrollMsg = "Enrollment Mode";
const char* scanMsg = "Scanning Mode";

// System Modes
enum SystemMode {MODE_SCAN, MODE_ENROLL};
SystemMode currentMode = MODE_SCAN;

void beep(int duration, int count = 1) {
  for (int i = 0; i < count; i++) {
    digitalWrite(buzzerPin, HIGH);
    delay(duration);
    digitalWrite(buzzerPin, LOW);
    if (count > 1) delay(100);
  }
}

void setup() {
  Serial.begin(115200);
  fingerSerial.begin(57600);
  
  lcd.init();
  lcd.backlight();
  pinMode(buzzerPin, OUTPUT);

  if (finger.verifyPassword()) {
    lcd.print("Sensor Connected");
    delay(1000);
  } else {
    lcd.print("Sensor Error");
    while (1);
  }
  
  showWelcomeScreen();
  printInstructions();
}

void loop() {
  handleModeChange();
  
  if (currentMode == MODE_SCAN) {
    handleScanMode();
  }
}

void handleModeChange() {
  if (Serial.available()) {
    char command = Serial.read();
    if (command == 'e') {
      enterEnrollmentMode();
    }
    else if (command == 's') {
      enterScanMode();
    }
  }
}

void handleScanMode() {
  int fingerprintID = getFingerprintID();
  
  if (fingerprintID > 0) {
    handleValidScan(fingerprintID);
  } 
  else if (fingerprintID == -1) {
    handleUnregisteredFingerprint();
  }
  
  showReadyScreen();
  delay(50);
}

void enterScanMode() {
  currentMode = MODE_SCAN;
  lcd.clear();
  lcd.print(scanMsg);
  beep(200, 1);
  delay(1000);
  showWelcomeScreen();
}

int getFingerprintID() {
  int p = finger.getImage();
  if (p != FINGERPRINT_OK) return 0;

  p = finger.image2Tz();
  if (p != FINGERPRINT_OK) return 0;

  p = finger.fingerFastSearch();
  return (p == FINGERPRINT_OK) ? finger.fingerID : -1;
}

void handleValidScan(int fid) {
  Serial.print("!{\"fid\":");
  Serial.print(fid);
  Serial.println("}");

  unsigned long startTime = millis();
  while (!Serial.available() && millis() - startTime < 3000);

  if (Serial.available()) {
    String response = Serial.readStringUntil('\n');
    processResponse(response);
  } else {
    showMessage("No Server Response", 300, 3);
  }
}

void handleUnregisteredFingerprint() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(notRegisteredMsg);
  lcd.setCursor(0, 1);
  lcd.print(registeredMsg);
  beep(1000, 1);
  delay(2000);
}

void enterEnrollmentMode() {
  currentMode = MODE_ENROLL;
  lcd.clear();
  lcd.print(enrollMsg);
  lcd.setCursor(0, 1);
  lcd.print("Enter ID (1-127)");
  beep(200, 1);
  
  int id = readNumberFromSerial();
  if (id > 0 && id <= 127) {
    enrollFingerprint(id);
  }
  
  enterScanMode(); // Return to scan mode after enrollment
}

int readNumberFromSerial() {
  int num = 0;
  while (num == 0) {
    if (Serial.available()) {
      num = Serial.parseInt();
      while (Serial.available()) Serial.read();
    }
    delay(100);
  }
  return num;
}

void enrollFingerprint(int id) {
  lcd.clear();
  lcd.print("Enrolling ID:");
  lcd.print(id);
  
  int p = -1;
  
  // First finger scan
  lcd.setCursor(0, 1);
  lcd.print("Place finger");
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    if (p == FINGERPRINT_NOFINGER) continue;
    if (p != FINGERPRINT_OK) {
      showError("Scan Error", p);
      return;
    }
  }

  p = finger.image2Tz(1);
  if (p != FINGERPRINT_OK) {
    showError("Image Messy", p);
    return;
  }

  lcd.setCursor(0, 1);
  lcd.print("Remove finger ");
  beep(200);
  delay(2000);

  // Second finger scan
  p = -1;
  lcd.setCursor(0, 1);
  lcd.print("Place again   ");
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    if (p == FINGERPRINT_NOFINGER) continue;
    if (p != FINGERPRINT_OK) {
      showError("Scan Error", p);
      return;
    }
  }

  p = finger.image2Tz(2);
  if (p != FINGERPRINT_OK) {
    showError("No Match", p);
    return;
  }

  p = finger.createModel();
  if (p != FINGERPRINT_OK) {
    showError("Not Matching", p);
    return;
  }

  p = finger.storeModel(id);
  if (p == FINGERPRINT_OK) {
    lcd.clear();
    lcd.print("Stored ID:");
    lcd.print(id);
    beep(200, 2);
    delay(2000);
  } else {
    showError("Store Error", p);
  }
}

void processResponse(String response) {
  lcd.clear();
  
  if (response.startsWith("{\"status\":\"success\"")) {
    int nameStart = response.indexOf("\"student\":\"") + 11;
    int nameEnd = response.indexOf("\"", nameStart);
    String studentName = response.substring(nameStart, nameEnd);
    
    int periodStart = response.indexOf("\"period\":\"") + 10;
    int periodEnd = response.indexOf("\"", periodStart);
    String period = response.substring(periodStart, periodEnd);
    
    lcd.print(studentName);
    lcd.setCursor(0, 1);
    lcd.print(period);
    beep(200, 2);
  } 
  else if (response.indexOf("not found") != -1) {
    lcd.print(errorMsg);
    beep(1000, 1);
  }
  else {
    lcd.print("System Error");
    lcd.setCursor(0, 1);
    lcd.print(response);
    beep(300, 3);
  }
  delay(2000);
}

void showWelcomeScreen() {
  lcd.clear();
  lcd.print(welcomeMsg);
  delay(2000);
  showReadyScreen();
}

void showReadyScreen() {
  lcd.clear();
  lcd.print(readyMsg);
  lcd.setCursor(0, 1);
  for (int i = 0; i < 16; i++) lcd.print("-");
}

void showMessage(const char* message, int beepDuration, int beepCount) {
  lcd.clear();
  lcd.print(message);
  beep(beepDuration, beepCount);
  delay(2000);
}

void showError(const char* message, int errorCode) {
  lcd.clear();
  lcd.print(message);
  lcd.setCursor(0, 1);
  lcd.print("Error: ");
  lcd.print(errorCode);
  beep(300, 3);
  delay(3000);
}

void printInstructions() {
  Serial.println("System Modes:");
  Serial.println("s - Enter scan mode");
  Serial.println("e - Enter enrollment mode");
  Serial.println("----------------------------");
}