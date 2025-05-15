<?php

declare(strict_types=1);

namespace App\Entity;

enum Locale: string
{
    case RUSSIAN = 'ru';
    case ENGLISH = 'en';

    public static function fromTelegramLanguageCode(?string $languageCode): self
    {
        return self::tryFrom($languageCode) ?? self::ENGLISH;
    }

    public function getCode(): string
    {
        return $this->value;
    }
}
