<?php

namespace App\Network\Max;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MaxHTTP implements MaxHTTPInterface
{
    /**
     * @var string
     */
    public string $url;

    /**
     * @var string
     */
    public string $token;

    /**
     * @var int
     */
    private int $httpTimeout;

    public function __construct()
    {
        $this->httpTimeout = env('MAX_BOT_TIMEOUT', 10);
        $this->url = env('MAX_BOT_HOST');
        $this->token = env('MAX_BOT_TOKEN');
    }

    /**
     * @param int $chatId
     * @param string $text
     *
     * @return Response
     */
    public function sendMessage(int $chatId, string $text): Response
    {
        $queryString = http_build_query([
            'chat_id' => $chatId,
            'access_token' => $this->token,
        ]);

        return Http::timeout($this->httpTimeout)
            ->post($this->url.'/messages?'.$queryString, [
                'format' => 'HTML',
                'text' => $text,
            ])
        ;
    }

    /**
     * @param int $chatId
     * @param string $messageId
     * @param string $text
     *
     * @return Response
     */
    public function editMessage(int $chatId, string $messageId, string $text): Response
    {
        $queryString = http_build_query([
            'chat_id' => $chatId,
            'access_token' => $this->token,
            'message_id' => $messageId,
        ]);

        return Http::timeout($this->httpTimeout)
            ->put($this->url.'/messages?'.$queryString, [
                'format' => 'HTML',
                'text' => $text,
            ])
        ;
    }
}
