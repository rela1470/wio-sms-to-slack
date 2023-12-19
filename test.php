<?php
namespace App;
date_default_timezone_set('Asia/Tokyo');
require __DIR__ . '/vendor/autoload.php';

$data = Env::get('SLACK_CHANNEL_ID');
var_dump($data);

$data = Env::get('SLACK_TOKEN');
var_dump($data);

$json = '
{
    "message": "\r\n\r\n+CMTI: \"ME\",0\r\n\r\n+CMGR: \"REC UNREAD\",\"09012345678\",,\"23/05/07,01:46:37+36\"\r\n3067304D305F308830FCFF01FF01FF0130843063305F30FCFF01FF01000A003100320033003400350036000A000A308F30FC3044308F30FC3044\r\n\r\nO"
}
';

    $array = Util::convertATEvent(json_decode($json, true));

    var_export($array);
