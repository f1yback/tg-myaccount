<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command\Callback;

use App\DTO\Telegram\TelegramCallbackQueryDTO;
use App\Entity\VpnProtocol;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use App\Service\User\UserManagerInterface;
use App\Service\Vpn\VpnConfigManagerInterface;
use Psr\Log\LoggerInterface;

class WireGuardProtocolCallbackCommand extends AbstractCallbackCommand
{
    private UserManagerInterface $userManager;
    private VpnConfigManagerInterface $vpnConfigManager;

    public function __construct(
        TelegramBotApiInterface $telegramBotApi,
        TranslationServiceInterface $translationService,
        LoggerInterface $logger,
        UserManagerInterface $userManager,
        VpnConfigManagerInterface $vpnConfigManager,
    ) {
        parent::__construct($telegramBotApi, $translationService, $logger);
        $this->userManager = $userManager;
        $this->vpnConfigManager = $vpnConfigManager;
    }

    public function supports(TelegramCallbackQueryDTO $callbackQuery): bool
    {
        return 'protocol_wireguard' === $callbackQuery->data;
    }

    public function execute(TelegramCallbackQueryDTO $callbackQuery): void
    {
        $this->handleProtocolSelection($callbackQuery, VpnProtocol::WIREGUARD);
    }

    private function handleProtocolSelection(TelegramCallbackQueryDTO $callbackQuery, VpnProtocol $protocol): void
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

        $this->logger->info('WireGuardProtocolCallbackCommand::handleProtocolSelection - Creating free trial config', [
            'user_id' => $user->getTelegramId(),
            'protocol' => $protocol->value,
            'chat_id' => $chatId,
        ]);

        try {
            $config = $this->vpnConfigManager->createFreeTrialConfig($user, $protocol);

            $protocolName = $this->translate('bot.protocol.'.str_replace('_', '_', $protocol->value), [], $locale);

            $message = $this->translate('bot.free_trial.created', [
                '%protocol%' => $protocolName,
                '%expires_at%' => $config->getExpiresAt()?->format('d.m.Y H:i'),
                '%config%' => $config->getConfigData(),
            ], $locale);

            $this->telegramBotApi->sendMessage($chatId, $message);

            $this->logger->info('WireGuardProtocolCallbackCommand::handleProtocolSelection - Free trial config created successfully', [
                'user_id' => $user->getTelegramId(),
                'config_id' => $config->getId()->toString(),
                'expires_at' => $config->getExpiresAt()?->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('WireGuardProtocolCallbackCommand::handleProtocolSelection - Failed to create free trial config', [
                'user_id' => $user->getTelegramId(),
                'protocol' => $protocol->value,
                'error' => $e->getMessage(),
            ]);

            $this->telegramBotApi->sendMessage(
                $chatId,
                $this->translate('bot.error.config_creation', ['%error%' => $e->getMessage()], $locale)
            );
        }
    }
}
