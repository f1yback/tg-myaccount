<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command\Callback;

use App\DTO\Telegram\TelegramCallbackQueryDTO;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use App\Service\User\UserManagerInterface;
use Psr\Log\LoggerInterface;

class FreeTrialCallbackCommand extends AbstractCallbackCommand
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
        return 'free_trial' === $callbackQuery->data;
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

        if (!$this->userManager->canGetFreeTrial($user)) {
            $this->telegramBotApi->sendMessage(
                $chatId,
                $this->translate('bot.free_trial.not_eligible', [], $locale)
            );

            return;
        }

        $text = $this->translate('bot.free_trial.choose_protocol', [], $locale);

        $inlineKeyboard = [
            [['text' => $this->translate('bot.free_trial.wireguard', [], $locale), 'callback_data' => 'protocol_wireguard']],
            [['text' => $this->translate('bot.free_trial.vless_reality', [], $locale), 'callback_data' => 'protocol_vless_reality']],
        ];

        $this->telegramBotApi->sendInlineKeyboard($chatId, $text, $inlineKeyboard);
    }
}
