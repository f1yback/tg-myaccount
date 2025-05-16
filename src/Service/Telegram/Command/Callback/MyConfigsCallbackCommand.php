<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command\Callback;

use App\DTO\Telegram\TelegramCallbackQueryDTO;
use App\Service\Telegram\TelegramBotApiInterface;
use App\Service\Telegram\TranslationServiceInterface;
use App\Service\User\UserManagerInterface;
use App\Service\Vpn\VpnConfigManagerInterface;
use Psr\Log\LoggerInterface;

class MyConfigsCallbackCommand extends AbstractCallbackCommand
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
        return 'my_configs' === $callbackQuery->data;
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
        $configs = $this->vpnConfigManager->getActiveConfigs($user);

        if (empty($configs)) {
            $this->telegramBotApi->sendMessage($chatId, $this->translate('bot.configs.none', [], $locale));

            return;
        }

        $message = $this->translate('bot.configs.list', [], $locale);

        foreach ($configs as $index => $config) {
            $protocolName = $this->translate('bot.protocol.'.$config->getProtocol()->value, [], $locale);
            $expiresAt = $config->getExpiresAt()?->format('d.m.Y H:i') ?? $this->translate('bot.configs.permanent', [], $locale);

            $message .= $this->translate('bot.configs.item', [
                '%number%' => $index + 1,
                '%protocol%' => $protocolName,
                '%expires%' => $expiresAt,
            ], $locale);
        }

        $this->telegramBotApi->sendMessage($chatId, $message);
    }
}
