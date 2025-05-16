<?php

declare(strict_types=1);

namespace App\Service\Telegram;

use App\Exception\Telegram\SendDocumentException;
use App\Exception\Telegram\SendMessageException;
use App\Exception\Telegram\WebhookException;

interface TelegramBotApiInterface
{
    /**
     * @param array<array-key, mixed> $options
     *
     * @throws SendMessageException
     */
    public function sendMessage(int $chatId, string $text, array $options = []): void;

    /** @param array<array-key, mixed> $keyboard */
    public function sendKeyboard(int $chatId, string $text, array $keyboard): void;

    /** @param array<array-key, mixed> $inlineKeyboard */
    public function sendInlineKeyboard(int $chatId, string $text, array $inlineKeyboard): void;

    /**
     * @throws SendDocumentException
     */
    public function sendDocument(int $chatId, string $documentPath, string $caption = ''): void;

    /** @return array<array-key, mixed>|null */
    public function getWebhookUpdate(): ?array;

    /**
     * @throws WebhookException
     */
    public function setWebhook(string $url): string;

    /**
     * @throws WebhookException
     */
    public function deleteWebhook(): bool;
}
