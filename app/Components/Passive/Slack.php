<?php

namespace App\Components\Passive;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class Slack 
{
    public static function reportError($exception)
    {
        Log::debug("errror");
        if(empty(config('logging.slack.url')))
            return;

        $payload = [
            "channel" => config('logging.slack.channel'),
            "username" => config('logging.slack.username'),
            "text" => $exception->getMessage(),
            "icon_emoji" => config('logging.slack.emoji'),//":ghost:"  ":boom:"
            "blocks" => [
                [
                    "type" => "header",
                    "text" => [
                        "type" => "plain_text",
                        "text" => "Error Reported!",
                    ]
                ],[
                    "type" => "section",
                    "fields" => [ 
                        [
                            "type" => "mrkdwn",
                            "text" => "*Error Code:* " . $exception->getCode()
                        ]  
                    ]
                ],[
                    "type" => "section",
                    "fields" => [   
                         [
                            "type" => "mrkdwn",
                            "text" => "*Error Message:*\n" . $exception->getMessage()
                        ]
                    ]
                ],[
                    "type" => "section",
                    "fields" => [ 
                        [
                            "type" => "mrkdwn",
                            "text" => "\n<!date^".Carbon::now()->timestamp."^ *Created at:* {date_num} {time_secs}|*Created at:* ".Carbon::now().">"
                        ]
                    ]
                ] 
            ]
        ];

        $client = new Client();
        $client->request('POST', 
        config('logging.slack.url'), 
        [
            'json' => $payload,
        ]);  
    } 
}