# Wio SMS to Slack
* Receives SMS with Wio LTE hardware and forwards it to Slack.
* Wio LTEのハードウェアを通じてSMSを受信し、Slackに転送するシステムです。

# Warning
* There is a potential risk of information leakage when using multi-factor authentication with this project.
* Reduce risk by keeping your communications confidential. As an example, the author uses SORACOM Funk to communicate between the device and AWS in a closed network.
* When copying, please conduct a risk assessment at your own risk.
* 本プロジェクトを用いて多要素認証を利用すると、情報漏洩の潜在的なリスクとなり得ます。
* 通信の秘匿化をしてリスクを低減してください。例として、作者はSORACOM Funkを通じて、デバイスとAWSの通信を閉塞網で行っています。
* もし模倣する際は、再度ご自身の責任でリスクアセスメントを実施頂きますよう、お願いいたします。

## Recommend Hardware
* SORACOM IoT SIM Plan-D-300MB nano SMS/Data
  * https://soracom.jp/store/13380/
* Wio LTE JP Version 
  * https://soracom.jp/store/5301/

## Recommend Software
* [SORACOM Funk](https://soracom.jp/services/funk/)
  * This is a product that communicates between AWS and devices using a closed network. Although not required, it is strongly recommended.
  * AWSとデバイス間を閉塞網で通信するプロダクトです。必須では有りませんが、強く推奨します。

## Require Software
* AWS Lambda
* AWS Secrets Manager (`SLACK_TOKEN` / `SLACK_CHANNEL_ID`)
* [Bref 3](https://bref.sh/)
  * include PHP 8.5
  * include [osls](https://github.com/oss-serverless/serverless) 4.x (Serverless Framework v3 fork)
    * include Node.js `^20.19.0 || ^22.13.0 || >=24`

# Sample
## SMS Receive
```text
+CMGR: "REC UNREAD","09012345678",,"23/05/07,01:46:37+36"
3067304D305F308830FCFF01FF01FF0130843063305F30FCFF01FF01000A003100320033003400350036000A000A308F30FC3044308F30FC3044
O
```
## Send Lambda
```json
{"message": "\r\n\r\n+CMTI: \"ME\",0\r\n\r\n+CMGR: \"REC UNREAD\",\"09012345678\",,\"23/05/07,01:46:37+36\"\r\n3067304D305F308830FCFF01FF01FF0130843063305F30FCFF01FF01000A003100320033003400350036000A000A308F30FC3044308F30FC3044\r\n\r\nO"}
```

## PHP Result
```phpt
*** SMS convert ***
+CMGR: "REC UNREAD","09012345678",,"23/05/07,01:46:37+36"
*** SMS convert result ***
[{"phone":"09012345678","date":"2023-05-07 01:46:37","text":"\u3067\u304d\u305f\u3088\u30fc\uff01\uff01\uff01\u3084\u3063\u305f\u30fc\uff01\uff01\n123456\n\n\u308f\u30fc\u3044\u308f\u30fc\u3044","origin_header":"\"+CMGR: \\\"REC UNREAD\\\",\\\"09012345678\\\",,\\\"23\\\/05\\\/07,01:46:37+36\\\"\"","origin_text":"3067304D305F308830FCFF01FF01FF0130843063305F30FCFF01FF01000A003100320033003400350036000A000A308F30FC3044308F30FC3044"}]
array (
  0 => 
  array (
    'phone' => '09012345678',
    'date' => '2023-05-07 01:46:37',
    'text' => 'できたよー！！！やったー！！
123456

わーいわーい',
    'origin_header' => '"+CMGR: \\"REC UNREAD\\",\\"09012345678\\",,\\"23\\/05\\/07,01:46:37+36\\""',
    'origin_text' => '3067304D305F308830FCFF01FF01FF0130843063305F30FCFF01FF01000A003100320033003400350036000A000A308F30FC3044308F30FC3044',
  ),
) 
```
# How To

## Setup Server
* Node.js install (osls)
* PHP 8.5 install
  * https://www.php.net/
* Composer install
  * https://getcomposer.org/
 

## Setup Hardware
* Setup devPC
  * https://users.soracom.io/ja-jp/guides/dev-boards/wio-lte/development-environment/
* include `Wio LTE for Arduino` and `ArduinoJson`
* compilation
* upload

## Deploy Server
```shell
composer install
npm install

## aws login

## Secrets Manager に Secret を作る (名前はサービス名と同じ。値は JSON)
## {"SLACK_TOKEN": "xoxb-...", "SLACK_CHANNEL_ID": "C..."}
aws secretsmanager create-secret --name sms-to-slack --secret-string file://secret.json
rm secret.json

npx osls deploy
## 既存のスタック名・関数名に合わせる場合
## SERVICE_NAME=<service> npx osls deploy
```

* `.env` is for local use only and is not included in the Lambda package.
* `.env` はローカル確認用です。Lambda のパッケージには含めません (`cp -a .env_sample .env`)。
* The SMS body, phone number and event are not written to the logs (SMS may contain one-time passwords).
* SMS の本文・電話番号・event はログに出しません (ワンタイムパスワードが含まれることがあるため)。

## (optional) Setup SORACOM Funk
* connect device to aws lambda
* https://users.soracom.io/ja-jp/docs/funk/

## Special Thanks
* https://github.com/SeeedJP/WioLTEforArduino
* https://qiita.com/ma2shita/items/a50f6b2b1cc438b1f8b7
* https://www.instructables.com/two-ways-to-reset-arduino-in-software/
* https://arduinojson.org/
* https://github.com/SeeedJP/WioLTEforArduino

## Author
* @rela1470
  * https://rela1470.hatenablog.jp/
  * https://twitter.com/rela1470