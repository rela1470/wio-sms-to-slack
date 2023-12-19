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

 */
return function ($event) {
    try{

        error_log('*** event ***');
        error_log(json_encode($event));

        error_log('*** convertSMS ***');
        $smsArray = Util::convertATEvent($event);

        foreach ($smsArray as $sms) {
            $slack = new Slack(Env::get('SLACK_TOKEN'));
            $slack->sendMessage(Env::get('SLACK_CHANNEL_ID'), 'SMS Received!', $slack->createBlockKit($sms));
        }

    } catch (Exception $e) {
        error_log('[ERROR]' . $e->getMessage());
        http_response_code( 500 );
        exit;
    }

    error_log('*** Done! ***');
    return 'OK';
};