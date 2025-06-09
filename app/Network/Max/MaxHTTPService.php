<?php

namespace App\Network\Max;

use App\Core\Errors;
use App\Exceptions\ApplicationException;
use App\Exceptions\BadRequestException;
use App\Helper\Log\TelegramLogHelper;
use Psr\SimpleCache\InvalidArgumentException;

class MaxHTTPService implements MaxHTTPServiceInterface
{
    /**
     * @param MaxHTTPInterface $http
     */
    public function __construct(
        public MaxHTTPInterface $http,
    ) {}

    /**
     * @param int $chatId
     * @param string $text
     *
     * @return null|array
     */
    public function sendMessage(int $chatId, string $text): ?array
    {
        try {
            $response = $this->http->sendMessage($chatId, $text);
        } catch (\Throwable $e) {
            throw new BadRequestException(TelegramLogHelper::hideBotInfo($e->getMessage()), Errors::MAX_REQUEST_EXCEPTION->value);
        }
        if (!$response->ok()) {
            $message = $response->json('message');

            throw new ApplicationException('Max response error:'.$message, Errors::MAX_RESPONSE_ERROR->value);
        }

        $messageId = $response->json('message')['body']['mid'];

        return [
            'message_id' => $messageId,
        ];
    }

    /**
     * @param int $chatId
     * @param string $messageId
     * @param string $text
     *
     * @return null|array
     *
     * @throws InvalidArgumentException
     */
    public function editMessage(int $chatId, string $messageId, string $text): ?array
    {
        if (!$this->isUniqueSend($chatId, $messageId, $text)) {
            return null;
        }

        try {
            $response = $this->http->editMessage($chatId, $messageId, $text);
        } catch (\Throwable $e) {
            throw new BadRequestException(TelegramLogHelper::hideBotInfo($e->getMessage()), Errors::MAX_REQUEST_EXCEPTION->value);
        }

        if (!$response->ok()) {
            $message = $response->json('message');

            throw new ApplicationException('Max response error: '.$message, Errors::MAX_RESPONSE_ERROR->value);
        }

        $messageId = $response->json('message')['body']['mid'];

        return [
            'message_id' => $messageId,
        ];
    }

    /**
     * @param int $chatId
     * @param string $messageId
     * @param string $text
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    private function isUniqueSend(int $chatId, string $messageId, string $text): bool
    {
        if (\Cache::get($chatId.'_'.$messageId) == md5($text)) {
            return false;
        }
        \Cache::set($chatId.'_'.$messageId, md5($text), 60 * 5);

        return true;
    }
}
