<?php

declare(strict_types=1);

namespace App\Service\Telegram;

use App\Exception\Telegram\SendDocumentException;
use App\Exception\Telegram\SendMessageException;
use App\Exception\Telegram\WebhookException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class TelegramBotApiAdapter implements TelegramBotApiInterface
{
    private BotApi $botApi;

    public function __construct(string $botToken)
    {
        $this->botApi = new BotApi($botToken);
    }

    /** @param array<array-key, mixed> $options */
    public function sendMessage(int $chatId, string $text, array $options = []): void
    {
        try {
            $this->botApi->sendMessage($chatId, $text);
        } catch (Exception $e) {
            throw new SendMessageException('Failed to send message: '.$e->getMessage(), $e);
        }
    }

    /** @param array<array-key, mixed> $keyboard */
    public function sendKeyboard(int $chatId, string $text, array $keyboard): void
    {
        $replyMarkup = new ReplyKeyboardMarkup($keyboard, true, true);
        $this->sendMessage($chatId, $text, ['reply_markup' => $replyMarkup]);
    }

    /** @param array<array-key, mixed> $inlineKeyboard */
    public function sendInlineKeyboard(int $chatId, string $text, array $inlineKeyboard): void
    {
        $inlineMarkup = new InlineKeyboardMarkup($inlineKeyboard);
        $this->sendMessage($chatId, $text, ['reply_markup' => $inlineMarkup]);
    }

    public function sendDocument(int $chatId, string $documentPath, string $caption = ''): void
    {
        try {
            $this->botApi->sendDocument($chatId, new \CURLFile($documentPath), $caption);
        } catch (Exception $e) {
            throw new SendDocumentException('Failed to send document: '.$e->getMessage(), $e);
        }
    }

    /** @return array<array-key, mixed>|null */
    public function getWebhookUpdate(): ?array
    {
        $input = file_get_contents('php://input');
        if (false === $input) {
            return null;
        }

        $update = json_decode($input, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return null;
        }

        return $update;
    }

    public function setWebhook(string $url): string
    {
        try {
            return $this->botApi->setWebhook($url);
        } catch (Exception $e) {
            throw new WebhookException('Failed to set webhook: '.$e->getMessage(), $e);
        }
    }

    public function deleteWebhook(): bool
    {
        try {
            return $this->botApi->deleteWebhook();
        } catch (Exception $e) {
            throw new WebhookException('Failed to delete webhook: '.$e->getMessage(), $e);
        }
    }
}
