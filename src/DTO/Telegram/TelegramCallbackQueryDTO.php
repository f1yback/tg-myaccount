<?php

declare(strict_types=1);

namespace App\DTO\Telegram;

final readonly class TelegramCallbackQueryDTO
{
    public function __construct(
        public string $id,
        public ?TelegramUserDTO $user,
        public string $data,
        public ?TelegramMessageDTO $message = null,
    ) {
    }

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            user: TelegramUserDTO::fromArray($data['from']),
            data: $data['data'],
            message: isset($data['message']) ? TelegramMessageDTO::fromArray($data['message']) : null,
        );
    }
}
