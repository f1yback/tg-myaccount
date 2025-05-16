<?php

declare(strict_types=1);

namespace App\Service\Telegram;

use App\Entity\Locale;

interface TranslationServiceInterface
{
    /** @param array<array-key, mixed> $parameters */
    public function translate(string $key, array $parameters = [], ?string $locale = null): string;

    public function getUserLocale(?string $languageCode): Locale;
}
