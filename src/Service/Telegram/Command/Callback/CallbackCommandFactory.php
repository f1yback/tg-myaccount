<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command\Callback;

use App\DTO\Telegram\TelegramCallbackQueryDTO;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use App\Service\User\UserManagerInterface;
use App\Service\Vpn\VpnConfigManagerInterface;
use Psr\Log\LoggerInterface;

class CallbackCommandFactory
{
    /** @var AbstractCallbackCommand[] */
    private array $commands = [];

    private VpnConfigManagerInterface $vpnConfigManager;

    public function __construct(
        private readonly TelegramBotApiInterface $telegramBotApi,
        private readonly TranslationServiceInterface $translationService,
        private readonly UserManagerInterface $userManager,
        VpnConfigManagerInterface $vpnConfigManager,
        private readonly LoggerInterface $logger,
    ) {
        $this->vpnConfigManager = $vpnConfigManager;
        $this->initializeCommands();
    }

    private function initializeCommands(): void
    {
        $this->commands[] = new FreeTrialCallbackCommand(
            $this->telegramBotApi,
            $this->translationService,
            $this->logger,
            $this->userManager
        );

        $this->commands[] = new MyConfigsCallbackCommand(
            $this->telegramBotApi,
            $this->translationService,
            $this->logger,
            $this->userManager,
            $this->vpnConfigManager
        );

        $this->commands[] = new AddBalanceCallbackCommand(
            $this->telegramBotApi,
            $this->translationService,
            $this->logger,
            $this->userManager
        );

        $this->commands[] = new WireGuardProtocolCallbackCommand(
            $this->telegramBotApi,
            $this->translationService,
            $this->logger,
            $this->userManager,
            $this->vpnConfigManager
        );

        $this->commands[] = new VlessRealityProtocolCallbackCommand(
            $this->telegramBotApi,
            $this->translationService,
            $this->logger,
            $this->userManager,
            $this->vpnConfigManager
        );
    }

    public function getCommandForCallback(TelegramCallbackQueryDTO $callbackQuery): ?AbstractCallbackCommand
    {
        foreach ($this->commands as $command) {
            if ($command->supports($callbackQuery)) {
                return $command;
            }
        }

        return null;
    }
}
