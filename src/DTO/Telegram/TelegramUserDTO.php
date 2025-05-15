<?php

declare(strict_types=1);

namespace App\DTO\Telegram;

final readonly class TelegramUserDTO
{
    public function __construct(
        public int $id,
        public ?string $username = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $languageCode = null,
    ) {
    }

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            username: $data['username'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            languageCode: $data['language_code'] ?? null,
        );
    }
}
