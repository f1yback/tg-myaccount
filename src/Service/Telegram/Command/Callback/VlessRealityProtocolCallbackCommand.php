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

class VlessRealityProtocolCallbackCommand extends AbstractCallbackCommand
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
        return 'protocol_vless_reality' === $callbackQuery->data;
    }

    public function execute(TelegramCallbackQueryDTO $callbackQuery): void
    {
        $this->handleProtocolSelection($callbackQuery, VpnProtocol::VLESS_REALITY);
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

        try {
            $config = $this->vpnConfigManager->createFreeTrialConfig($user, $protocol);

            $protocolName = $this->translate('bot.protocol.'.$protocol->value, [], $locale);

            $message = $this->translate('bot.free_trial.created', [
                '%protocol%' => $protocolName,
                '%expires_at%' => $config->getExpiresAt()?->format('d.m.Y H:i'),
                '%config%' => $config->getConfigData(),
            ], $locale);

            $this->telegramBotApi->sendMessage($chatId, $message);
        } catch (\Exception $e) {
            $this->telegramBotApi->sendMessage(
                $chatId,
                $this->translate('bot.error.config_creation', ['%error%' => $e->getMessage()], $locale)
            );
        }
    }
}
