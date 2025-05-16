<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command;

use App\DTO\Telegram\TelegramUpdateDTO;
use App\Service\Telegram\Command\Callback\CallbackCommandFactory;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use App\Service\User\UserManagerInterface;
use App\Service\Vpn\VpnConfigManagerInterface;
use Psr\Log\LoggerInterface;

class BotCommandFactory
{
    /** @var AbstractBotCommand[] */
    private array $commands = [];

    public function __construct(
        private readonly TelegramBotApiInterface $telegramBotApi,
        private readonly TranslationServiceInterface $translationService,
        private readonly UserManagerInterface $userManager,
        private readonly VpnConfigManagerInterface $vpnConfigManager,
        private readonly LoggerInterface $logger,
    ) {
        $this->initializeCommands();
    }

    private function initializeCommands(): void
    {
        $this->commands[] = new StartCommand(
            $this->telegramBotApi,
            $this->translationService,
            $this->logger,
            $this->userManager
        );

        $callbackCommandFactory = new CallbackCommandFactory(
            $this->telegramBotApi,
            $this->translationService,
            $this->userManager,
            $this->vpnConfigManager,
            $this->logger
        );

        $this->commands[] = new CallbackQueryCommand(
            $this->telegramBotApi,
            $this->translationService,
            $callbackCommandFactory,
            $this->logger
        );
    }

    public function getCommandForUpdate(TelegramUpdateDTO $update): ?AbstractBotCommand
    {
        foreach ($this->commands as $command) {
            if ($command->supports($update)) {
                return $command;
            }
        }

        return null;
    }
}
