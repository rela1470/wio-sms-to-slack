<?php
namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;

class Slack {
    private static string $_oauthToken   = '';

    public function __construct(string $clientSecret)
    {
        self::$_oauthToken   = $clientSecret;
    }

    /**
     * createBlockKit
     * @param $data
     * @return string|false
     */
    public function createBlockKit($data) : bool|string
    {
        $array = [
            [
                "type" => "header",
                "text"=> [
                    "type"  => "plain_text",
                    "text"  => "SMS Received!",
                    "emoji" => true
                    ]
            ],
            [
                "type"=> "section",
                "fields"=> [
                    [
                        "type"=> "mrkdwn",
                        "text"=> "*Phone No:*\n{$data['phone']}"
                    ],
                    [
                        "type"=> "mrkdwn",
                        "text"=> "*Date:*\n{$data['date']}"
                    ]
                ]
            ],
            [
                "type"=> "section",
                "text"=> [
                "type"=> "mrkdwn",
                    "text"=> "*text:*\n{$data['text']}"
                ]
            ],
            [
                "type"=> "context",
                "elements"=> [
                    [
                        "type"=> "plain_text",
                        "text"=> "origin_header: {$data['origin_header']}",
                        "emoji"=> true
                    ]
                ]
            ],
            [
                "type"=> "context",
                "elements"=> [
                    [
                        "type"=> "plain_text",
                        "text"=> "origin_text: {$data['origin_text']}",
                        "emoji"=> true
                    ]
                ]
            ]
        ];

        return json_encode($array);
    }

    /**
     * sendMessage
     * @param $channelId
     * @param $text
     * @param string $blocksJson
     * @return StreamInterface
     * @throws GuzzleException
     * @throws \Exception
     */
    public function sendMessage($channelId, $text, string $blocksJson): StreamInterface
    {
        $url = 'https://slack.com/api/chat.postMessage';
        echo "[Slack SendMessage] $channelId \n";

        $body = [
            'channel' => $channelId,
            'text' => $text,
            'blocks' => $blocksJson,
        ];

        $http = new Client(['debug' => false]);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . self::$_oauthToken,
            ],
            'form_params' => $body
        ];

        $response = $http->post($url, $options);
        if ($response->getStatusCode() != 200) throw new \Exception('Slack sendMessage error');

        error_log("[Slack SendMessage] $channelId \n");

        return $response->getBody();
    }

}
