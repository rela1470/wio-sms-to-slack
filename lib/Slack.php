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
        // SMS は誰でも送れるので、mrkdwn のメンション (<!channel> 等) やリンクとして解釈させない
        $phone = self::escape($data['phone']);
        $date = self::escape($data['date']);
        $text = self::escape($data['text']);

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
                        "text"=> "*Phone No:*\n{$phone}"
                    ],
                    [
                        "type"=> "mrkdwn",
                        "text"=> "*Date:*\n{$date}"
                    ]
                ]
            ],
            [
                "type"=> "section",
                "text"=> [
                "type"=> "mrkdwn",
                    "text"=> "*text:*\n{$text}"
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
     * escape
     * @see https://api.slack.com/reference/surfaces/formatting#escaping
     * @param string $text
     * @return string
     */
    private static function escape(string $text): string
    {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $text);
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

        // Slack は invalid_auth などでも 200 を返すので ok を見る (送れていないのに SMS を消させない)
        $result = json_decode((string)$response->getBody(), true);
        if (! ($result['ok'] ?? false)) throw new \Exception('Slack sendMessage error: ' . ($result['error'] ?? 'unknown'));

        return $response->getBody();
    }

}
