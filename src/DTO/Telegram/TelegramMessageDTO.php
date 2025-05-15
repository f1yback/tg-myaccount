<?php

declare(strict_types=1);

namespace App\DTO\Telegram;

final readonly class TelegramMessageDTO
{
    public function __construct(
        public int $messageId,
        public int $chatId,
        public TelegramUserDTO $user,
        public ?string $text = null,
        public ?\DateTimeImmutable $date = null,
    ) {
    }

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            messageId: $data['message_id'],
            chatId: $data['chat']['id'],
            user: TelegramUserDTO::fromArray($data['from']),
            text: $data['text'] ?? null,
            date: isset($data['date']) ? (new \DateTimeImmutable())->setTimestamp($data['date']) : null,
        );
    }
}
