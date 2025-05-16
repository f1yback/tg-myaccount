<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command\Callback;

use App\DTO\Telegram\TelegramCallbackQueryDTO;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractCallbackCommand
{
    public function __construct(
        protected readonly TelegramBotApiInterface $telegramBotApi,
        protected readonly TranslationServiceInterface $translationService,
        protected readonly LoggerInterface $logger,
    ) {
    }

    abstract public function supports(TelegramCallbackQueryDTO $callbackQuery): bool;

    abstract public function execute(TelegramCallbackQueryDTO $callbackQuery): void;

    /** @param array<array-key, mixed> $parameters */
    /** @param array<array-key, mixed> $parameters */
    protected function translate(string $key, array $parameters = [], ?\App\Entity\Locale $locale = null): string
    {
        return $this->translationService->translate($key, $parameters, $locale?->getCode());
    }

    protected function getUserLocale(TelegramCallbackQueryDTO $callbackQuery): \App\Entity\Locale
    {
        $user = $callbackQuery->user;

        return $this->translationService->getUserLocale($user?->languageCode);
    }
}
