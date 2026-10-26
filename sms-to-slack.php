<?php declare(strict_types=1);

namespace App;
date_default_timezone_set('Asia/Tokyo');
require __DIR__ . '/vendor/autoload.php';


/**
 * call lambda Event
 * sample

+CMGR: "REC READ","09000000000",,"23/04/29,01:21:32+36"
306630593068
O

 * SMS には多要素認証のコードなどが入るため、本文・電話番号・event はログに出さない
 * Slack に送れなかったときは例外のまま投げる (Funk がエラーを返し、デバイスは SMS を消さずに再送する)
 */
return function ($event) {
    $smsArray = Util::convertATEvent(is_array($event) ? $event : []);
    if (! $smsArray) {
        error_log('[sms] no sms');
        return 'OK';
    }

    $token = Env::get('SLACK_TOKEN');
    $channelId = Env::get('SLACK_CHANNEL_ID');
    if (! $token || ! $channelId) throw new \RuntimeException('SLACK_TOKEN / SLACK_CHANNEL_ID is not set');

    $slack = new Slack($token);
    foreach ($smsArray as $sms) {
        $slack->sendMessage($channelId, 'SMS Received!', $slack->createBlockKit($sms));
    }

    error_log('[sms] sent ' . count($smsArray));
    return 'OK';
};
