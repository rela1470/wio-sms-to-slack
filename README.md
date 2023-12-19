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
* [Bref 2.0](https://bref.sh/)
  * include PHP 8.1
  * include [serverless framework v3](https://www.serverless.com/)
    * include nodejs14.x

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
* serverless CLI install
  * https://www.serverless.com/
* PHP 8.1 install
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
cp -a .env_sample .env
vi .env

## setup slack env
## aws login

serverless deploy
```

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