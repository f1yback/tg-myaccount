<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command;

use App\DTO\Telegram\TelegramUpdateDTO;
use App\Entity\Locale;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractBotCommand
{
    public function __construct(
        protected readonly TelegramBotApiInterface $telegramBotApi,
        protected readonly TranslationServiceInterface $translationService,
        protected readonly LoggerInterface $logger,
    ) {
    }

    abstract public function supports(TelegramUpdateDTO $update): bool;

    abstract public function execute(TelegramUpdateDTO $update): void;

    /** @param array<array-key, mixed> $parameters */
    protected function translate(string $key, array $parameters = [], ?Locale $locale = null): string
    {
        return $this->translationService->translate($key, $parameters, $locale?->getCode());
    }

    protected function getUserLocale(TelegramUpdateDTO $update): Locale
    {
        $user = $update->getUser();

        return $this->translationService->getUserLocale($user?->languageCode);
    }
}
