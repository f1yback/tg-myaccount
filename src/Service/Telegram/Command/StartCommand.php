<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command;

use App\DTO\Telegram\TelegramUpdateDTO;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use App\Service\User\UserManagerInterface;
use Psr\Log\LoggerInterface;

class StartCommand extends AbstractBotCommand
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

    public function supports(TelegramUpdateDTO $update): bool
    {
        $message = $update->message;

        return null !== $message && str_starts_with($message->text ?? '', '/start');
    }

    public function execute(TelegramUpdateDTO $update): void
    {
        $message = $update->message;
        if (null === $message) {
            $this->logger->warning('StartCommand::execute - No message in update');

            return;
        }

        $userData = $message->user;
        $chatId = $message->chatId;
        $locale = $this->getUserLocale($update);

        // Safety checks (though PHPStan knows these can't be null due to DTO design)
        if (null === $userData) { // @phpstan-ignore-line
            $this->logger->warning('StartCommand::execute - Invalid message data: missing userData');

            return;
        }
        if (null === $chatId) { // @phpstan-ignore-line
            $this->logger->warning('StartCommand::execute - Invalid message data: missing chatId');

            return;
        }

        $this->logger->info('StartCommand::execute - Processing /start command', [
            'user_id' => $userData->id,
            'chat_id' => $chatId,
            'username' => $userData->username,
            'locale' => $locale->getCode(),
        ]);

        $user = $this->userManager->findOrCreateUser(
            $userData->id,
            $userData->username,
            $userData->firstName,
            $userData->lastName
        );

        $welcomeText = $this->translate('bot.start.welcome', [
            '%balance%' => $user->getBalance(),
        ], $locale);

        $keyboard = [
            [['text' => $this->translate('bot.start.menu.free_trial', [], $locale), 'callback_data' => 'free_trial']],
            [['text' => $this->translate('bot.start.menu.my_configs', [], $locale), 'callback_data' => 'my_configs']],
            [['text' => $this->translate('bot.start.menu.add_balance', [], $locale), 'callback_data' => 'add_balance']],
        ];

        $this->telegramBotApi->sendKeyboard($chatId, $welcomeText, $keyboard);

        $this->logger->info('StartCommand::execute - Start command processed successfully', [
            'user_id' => $user->getTelegramId(),
            'balance' => $user->getBalance(),
        ]);
    }
}
