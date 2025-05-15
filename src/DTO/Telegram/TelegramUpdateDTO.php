<?php

declare(strict_types=1);

namespace App\DTO\Telegram;

final readonly class TelegramUpdateDTO
{
    public function __construct(
        public int $updateId,
        public ?TelegramMessageDTO $message = null,
        public ?TelegramCallbackQueryDTO $callbackQuery = null,
    ) {
    }

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            updateId: $data['update_id'],
            message: isset($data['message']) ? TelegramMessageDTO::fromArray($data['message']) : null,
            callbackQuery: isset($data['callback_query']) ? TelegramCallbackQueryDTO::fromArray($data['callback_query']) : null,
        );
    }

    public function getUser(): ?TelegramUserDTO
    {
        return $this->message?->user ?? $this->callbackQuery?->user ?? null;
    }

    public function getChatId(): ?int
    {
        return $this->message?->chatId ?? $this->callbackQuery?->message?->chatId ?? null;
    }
}
