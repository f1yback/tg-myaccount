<?php

declare(strict_types=1);

namespace App\Service\Telegram;

use App\Entity\Locale;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService implements TranslationServiceInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /** @param array<array-key, mixed> $parameters */
    public function translate(string $key, array $parameters = [], ?string $locale = null): string
    {
        return $this->translator->trans($key, $parameters, 'messages', $locale);
    }

    public function getUserLocale(?string $languageCode): Locale
    {
        return Locale::fromTelegramLanguageCode($languageCode);
    }
}
