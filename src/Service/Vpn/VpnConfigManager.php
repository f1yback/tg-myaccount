<?php

declare(strict_types=1);

namespace App\Service\Vpn;

use App\Entity\User;
use App\Entity\VpnConfig;
use App\Entity\VpnProtocol;
use App\Exception\Vpn\FreeTrialNotEligibleException;
use App\Repository\Vpn\VpnConfigRepositoryInterface;
use Psr\Log\LoggerInterface;

class VpnConfigManager implements VpnConfigManagerInterface
{
    public function __construct(
        private readonly VpnConfigRepositoryInterface $vpnConfigRepository,
        private readonly VpnApiClientInterface $vpnApiClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createFreeTrialConfig(User $user, VpnProtocol $protocol): VpnConfig
    {
        $this->logger->info('VpnConfigManager::createFreeTrialConfig - Creating free trial config', [
            'user_id' => $user->getTelegramId(),
            'protocol' => $protocol->value,
        ]);

        if (!$this->canCreateFreeTrial($user)) {
            $this->logger->warning('VpnConfigManager::createFreeTrialConfig - User not eligible for free trial', [
                'user_id' => $user->getTelegramId(),
            ]);
            throw new FreeTrialNotEligibleException('User is not eligible for free trial');
        }

        $configData = $this->generateConfigData($user->getTelegramId(), $protocol);
        $expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('PT24H'));

        $vpnConfig = new VpnConfig($user, $protocol, $configData, $expiresAt);
        $this->vpnConfigRepository->save($vpnConfig);

        $this->logger->info('VpnConfigManager::createFreeTrialConfig - Free trial config created successfully', [
            'user_id' => $user->getTelegramId(),
            'config_id' => $vpnConfig->getId()->toString(),
            'protocol' => $protocol->value,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);

        return $vpnConfig;
    }

    private function canCreateFreeTrial(User $user): bool
    {
        $activeConfigs = $this->vpnConfigRepository->findActiveByUser($user);

        return empty($activeConfigs);
    }

    private function generateConfigData(int $userId, VpnProtocol $protocol): string
    {
        return match ($protocol) {
            VpnProtocol::WIREGUARD => $this->vpnApiClient->createWireGuardConfig($userId),
            VpnProtocol::VLESS_REALITY => $this->vpnApiClient->createVlessRealityConfig($userId),
        };
    }

    /** @return array<VpnConfig> */
    public function getActiveConfigs(User $user): array
    {
        return $this->vpnConfigRepository->findActiveByUser($user);
    }

    public function downloadConfig(VpnConfig $config): string
    {
        if (!$config->isActive()) {
            throw new \DomainException('Config is not active');
        }

        return $config->getConfigData();
    }

    public function expireOldConfigs(): int
    {
        $expiredConfigs = $this->vpnConfigRepository->findExpiredConfigs();
        $count = 0;

        foreach ($expiredConfigs as $config) {
            $config->expire();
            $this->vpnConfigRepository->save($config);
            ++$count;
        }

        return $count;
    }

    public function revokeConfig(VpnConfig $config): void
    {
        $config->revoke();
        $this->vpnConfigRepository->save($config);

        // TODO: Call VPN API to revoke config on provider side
    }
}
