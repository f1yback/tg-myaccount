<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command\Callback;

use App\DTO\Telegram\TelegramCallbackQueryDTO;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use App\Service\User\UserManagerInterface;
use Psr\Log\LoggerInterface;

class AddBalanceCallbackCommand extends AbstractCallbackCommand
{
    private UserManagerInterface $userManager;

    public function __construct(
        TelegramBotApiInterface $telegramBotApi,
        TranslationServiceInterface $translationService,
        LoggerInterface $logger,
        UserManagerInterface $userManager,
    ) {
        parent::__construct($telegramBotApi, $translationService, $logger);
        $this->userManager = $userManager;
    }

    public function supports(TelegramCallbackQueryDTO $callbackQuery): bool
    {
        return 'add_balance' === $callbackQuery->data;
    }

    public function execute(TelegramCallbackQueryDTO $callbackQuery): void
    {
        $userData = $callbackQuery->user;
        $locale = $this->getUserLocale($callbackQuery);

        $user = $this->userManager->findOrCreateUser(
            $userData->id,
            $userData->username,
            $userData->firstName,
            $userData->lastName
        );

        $chatId = $callbackQuery->message?->chatId ?? 0;

        $message = $this->translate('bot.balance.current', [
            '%balance%' => $user->getBalance(),
        ], $locale);

        $this->telegramBotApi->sendMessage($chatId, $message);
    }
}
