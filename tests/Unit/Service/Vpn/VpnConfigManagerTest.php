<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Vpn;

use App\Entity\User;
use App\Entity\VpnConfig;
use App\Entity\VpnProtocol;
use App\Exception\Vpn\FreeTrialNotEligibleException;
use App\Repository\Vpn\VpnConfigRepositoryInterface;
use App\Service\Vpn\VpnApiClientInterface;
use App\Service\Vpn\VpnConfigManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class VpnConfigManagerTest extends TestCase
{
    private VpnConfigRepositoryInterface&MockObject $repository;
    private VpnApiClientInterface&MockObject $apiClient;
    private LoggerInterface&MockObject $logger;
    private VpnConfigManager $manager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VpnConfigRepositoryInterface::class);
        $this->apiClient = $this->createMock(VpnApiClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->manager = new VpnConfigManager(
            $this->repository,
            $this->apiClient,
            $this->logger
        );
    }

    public function testCreateFreeTrialConfigSuccess(): void
    {
        $user = new User(123);
        $protocol = VpnProtocol::WIREGUARD;
        $configData = 'wireguard-config-data';

        $this->repository
            ->expects($this->once())
            ->method('findActiveByUser')
            ->with($user)
            ->willReturn([]);

        $this->apiClient
            ->expects($this->once())
            ->method('createWireGuardConfig')
            ->with(123)
            ->willReturn($configData);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(VpnConfig::class));

        $result = $this->manager->createFreeTrialConfig($user, $protocol);

        $this->assertInstanceOf(VpnConfig::class, $result);
        $this->assertEquals($user, $result->getUser());
        $this->assertEquals($protocol, $result->getProtocol());
        $this->assertEquals($configData, $result->getConfigData());
        $this->assertNotNull($result->getExpiresAt());
    }

    public function testCreateFreeTrialConfigUserNotEligible(): void
    {
        $user = new User(123);
        $protocol = VpnProtocol::WIREGUARD;

        $existingConfig = new VpnConfig($user, VpnProtocol::WIREGUARD, 'existing-config');

        $this->repository
            ->expects($this->once())
            ->method('findActiveByUser')
            ->with($user)
            ->willReturn([$existingConfig]);

        $this->expectException(FreeTrialNotEligibleException::class);
        $this->expectExceptionMessage('User is not eligible for free trial');

        $this->manager->createFreeTrialConfig($user, $protocol);
    }

    /**
     * @dataProvider protocolProvider
     */
    public function testCreateFreeTrialConfigDifferentProtocols(VpnProtocol $protocol, string $expectedApiMethod): void
    {
        $user = new User(123);
        $configData = 'config-data';

        $this->repository
            ->expects($this->once())
            ->method('findActiveByUser')
            ->with($user)
            ->willReturn([]);

        $this->apiClient
            ->expects($this->once())
            ->method($expectedApiMethod)
            ->with(123)
            ->willReturn($configData);

        $this->repository
            ->expects($this->once())
            ->method('save');

        $result = $this->manager->createFreeTrialConfig($user, $protocol);

        $this->assertEquals($protocol, $result->getProtocol());
    }

    public static function protocolProvider(): array
    {
        return [
            'wireguard' => [VpnProtocol::WIREGUARD, 'createWireGuardConfig'],
            'vless_reality' => [VpnProtocol::VLESS_REALITY, 'createVlessRealityConfig'],
        ];
    }

    public function testGetActiveConfigs(): void
    {
        $user = new User(123);
        $expectedConfigs = [
            new VpnConfig($user, VpnProtocol::WIREGUARD, 'config1'),
            new VpnConfig($user, VpnProtocol::VLESS_REALITY, 'config2'),
        ];

        $this->repository
            ->expects($this->once())
            ->method('findActiveByUser')
            ->with($user)
            ->willReturn($expectedConfigs);

        $result = $this->manager->getActiveConfigs($user);

        $this->assertEquals($expectedConfigs, $result);
    }

    /**
     * @dataProvider downloadConfigProvider
     */
    public function testDownloadConfig(VpnConfig $config, string $expectedResult): void
    {
        $result = $this->manager->downloadConfig($config);
        $this->assertEquals($expectedResult, $result);
    }

    public static function downloadConfigProvider(): array
    {
        $user = new User(123);

        return [
            'wireguard config' => [
                new VpnConfig($user, VpnProtocol::WIREGUARD, 'wireguard-data'),
                'wireguard-data',
            ],
            'vless reality config' => [
                new VpnConfig($user, VpnProtocol::VLESS_REALITY, 'vless-data'),
                'vless-data',
            ],
            'empty config' => [
                new VpnConfig($user, VpnProtocol::WIREGUARD, ''),
                '',
            ],
        ];
    }
}
