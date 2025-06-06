<?php

namespace App\Network\Max;

use Illuminate\Http\Client\Response;

interface MaxHTTPInterface
{
    public function sendMessage(int $chatId, string $text): Response;

    public function editMessage(int $chatId, string $messageId, string $text): Response;
}
