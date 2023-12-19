/**
@see
https://github.com/SeeedJP/WioLTEforArduino
https://qiita.com/ma2shita/items/a50f6b2b1cc438b1f8b7
https://www.instructables.com/two-ways-to-reset-arduino-in-software/
*/

#define BAUDRATE 9600

#define CONSOLE SerialUSB
#define EC21J SerialModule
#include <ArduinoJson.h>
#include <WioLTEforArduino.h>
WioLTE Wio;

void(* resetFunc) (void) = 0;
void setup() {
  String message = "";
  EC21J.setTimeout(5000); // 5sec 
  
  delay(200);
  CONSOLE.begin(BAUDRATE);
  delay(200);

  CONSOLE.println("");
  CONSOLE.println("--- START ---------------------------------------------------");
  
  CONSOLE.println("### I/O Initialize.");
  Wio.Init();
  
  CONSOLE.println("### Power supply ON.");
  Wio.PowerSupplyLTE(true);
  delay(500);

  CONSOLE.println("### Turn on or reset.");
  if (!Wio.TurnOnOrReset()) {
    CONSOLE.println("### ERROR! ###");
    resetFunc();  //call reset
    return;
  }
  delay(3000);

  CONSOLE.println("### Get phone number.");
  char str[100];
  if (Wio.GetPhoneNumber(str, sizeof (str)) <= 0) {
    CONSOLE.println("### ERROR! ###");
    resetFunc();  //call reset
    return;
  }
  CONSOLE.println(str);

  CONSOLE.println("### Connecting to \"soracom.io\".");
  if (! Wio.Activate("soracom.io", "sora", "sora")) {
    CONSOLE.println("### ERROR! ###");
    resetFunc();  //call reset
    return;
  }

  //SET SMS PDU Mode
  CONSOLE.println("> AT+CMGF=1");
  EC21J.write("AT+CMGF=1");
  EC21J.write(0x0d); // send CR
  CONSOLE.println("### Serial Read...");
  message = EC21J.readStringUntil('OK');
  CONSOLE.println(message);

  CONSOLE.println("### Setup completed.");
}

void loop() {
  String message = "";

  //SMS Check
  CONSOLE.println("### SMS Check.");
  EC21J.write("AT+CMGR=0"); // SMS read
  EC21J.write(0x0d); // send CR
  CONSOLE.println("### Serial Read.");
  message = EC21J.readStringUntil('OK');
  CONSOLE.println(message);

  if (message.indexOf("+CMGR:") >= 0) {
    CONSOLE.println("### Found SMS.");

    //Send SORACOM Funk
    CONSOLE.println("### Send Funk.");
    String jsonString;
    DynamicJsonDocument jsonDoc(1024);
    jsonDoc["message"] = message;
    serializeJson(jsonDoc, jsonString);

    SerialUSB.print(jsonString.c_str());

    int status;
    if (!Wio.HttpPost("http://funk.soracom.io", jsonString.c_str(), &status)) {
      SerialUSB.println("### ERROR! ###");
      resetFunc();  //call reset
    }
    SerialUSB.print("### Status:");
    SerialUSB.println(status);

    if (status == 200) {
      SerialUSB.println("### Message Send OK!");

      //SMS Delete
      CONSOLE.println("### SMS Delete.");
      EC21J.write("AT+CMGD=0"); // SMS read
      EC21J.write(0x0d); // send CR
      CONSOLE.println("### Serial Read.");
      message = EC21J.readStringUntil('OK');
      CONSOLE.println(message);
    }
  }
  else {
    CONSOLE.println("### No Found SMS.");
  }

  delay(30000);

}