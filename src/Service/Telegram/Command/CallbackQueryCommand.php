<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command;

use App\DTO\Telegram\TelegramUpdateDTO;
use App\Service\Telegram\Command\Callback\CallbackCommandFactory;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use Psr\Log\LoggerInterface;

class CallbackQueryCommand extends AbstractBotCommand
{
    private CallbackCommandFactory $callbackCommandFactory;

    public function __construct(
        TelegramBotApiInterface $telegramBotApi,
        TranslationServiceInterface $translationService,
        CallbackCommandFactory $callbackCommandFactory,
        LoggerInterface $logger,
    ) {
        parent::__construct($telegramBotApi, $translationService, $logger);
        $this->callbackCommandFactory = $callbackCommandFactory;
    }

    public function supports(TelegramUpdateDTO $update): bool
    {
        return null !== $update->callbackQuery;
    }

    public function execute(TelegramUpdateDTO $update): void
    {
        $callbackQuery = $update->callbackQuery;
        $command = $this->callbackCommandFactory->getCommandForCallback($callbackQuery);

        if (null !== $command) {
            $command->execute($callbackQuery);
        }
    }
}
