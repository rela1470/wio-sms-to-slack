<?php
namespace App;

class Util {

    /**
     * convertATEvent
     * @param array $data
     * @return array
     */
    public static function convertATEvent(array $data): array
    {
        // SMS が無いときにデバイスから届く死活通知などは message を持たない
        if (! is_string($data['message'] ?? null)) return [];

        $dataLineArray = explode("\r\n", $data['message']);

        $smsArray = [];
        for ($i = 0; $i < count($dataLineArray); $i ++) {
            if (str_contains($dataLineArray[$i], '+CMGR:')) {
                $header = str_getcsv($dataLineArray[$i], ',', '"', '');
                $body = $dataLineArray[$i + 1] ?? '';
                $smsArray[] = [
                    'phone' => $header[1] ?? '',
                    'date' => Util::convertATDate($header[3] ?? ''),
                    'text' => Util::convertUCS2($body),
                    'origin_header' => json_encode($dataLineArray[$i]),
                    'origin_text' => $body,
                ];
            }
        }
        return $smsArray;
    }

    /**
     * convertATDate
     * @param string $date
     * @return string
     */
    public static function convertATDate(string $date) : string
    {
        $dateTime = \DateTime::createFromFormat('y/m/d,H:i:s+?', $date);
        if (! $dateTime) return $date;

        //TODO SORACOMは日本時間でズレないので不要
        //$timezone = explode('+', $date);
        //$dateTime->modify('+' . $timezone[1] * 15 . 'min');

        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * convertUCS2
     * @param string $text
     * @return string
     */
    public static function convertUCS2(string $text) : string
    {
        $message = '';
        $codeArray = str_split($text, 4);
        foreach ($codeArray as $code) $message .= mb_chr(hexdec($code), 'UTF-8');//ほんとはUCS-2だけどまあ互換あるっぽいしな

        return $message;
    }

}
