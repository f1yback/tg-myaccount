<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\VpnConfig;
use App\Entity\VpnProtocol;
use App\Entity\VpnStatus;
use PHPUnit\Framework\TestCase;

class VpnConfigTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User(123456789);
    }

    public function testVpnConfigCreation(): void
    {
        $protocol = VpnProtocol::WIREGUARD;
        $configData = 'wireguard-config-data';
        $expiresAt = new \DateTimeImmutable('+24 hours');

        $config = new VpnConfig($this->user, $protocol, $configData, $expiresAt);

        $this->assertEquals($this->user, $config->getUser());
        $this->assertEquals($protocol, $config->getProtocol());
        $this->assertEquals(VpnStatus::ACTIVE, $config->getStatus());
        $this->assertEquals($configData, $config->getConfigData());
        $this->assertEquals($expiresAt, $config->getExpiresAt());
        $this->assertTrue($config->isActive());
    }

    public function testVpnConfigStatusChange(): void
    {
        $config = new VpnConfig($this->user, VpnProtocol::WIREGUARD, 'config-data');

        $config->setStatus(VpnStatus::EXPIRED);
        $this->assertEquals(VpnStatus::EXPIRED, $config->getStatus());
        $this->assertFalse($config->isActive());

        $config->expire();
        $this->assertEquals(VpnStatus::EXPIRED, $config->getStatus());

        $config->revoke();
        $this->assertEquals(VpnStatus::REVOKED, $config->getStatus());
    }

    public function testStatusEnumValidation(): void
    {
        $config = new VpnConfig($this->user, VpnProtocol::WIREGUARD, 'config-data');

        // Test that all enum values work
        foreach (VpnStatus::cases() as $status) {
            $config->setStatus($status);
            $this->assertEquals($status, $config->getStatus());
        }
    }

    public function testIsExpired(): void
    {
        $pastDate = new \DateTimeImmutable('-1 hour');
        $futureDate = new \DateTimeImmutable('+1 hour');

        $expiredConfig = new VpnConfig($this->user, VpnProtocol::WIREGUARD, 'config-data', $pastDate);
        $activeConfig = new VpnConfig($this->user, VpnProtocol::WIREGUARD, 'config-data', $futureDate);
        $permanentConfig = new VpnConfig($this->user, VpnProtocol::WIREGUARD, 'config-data');

        $this->assertTrue($expiredConfig->isExpired());
        $this->assertFalse($activeConfig->isExpired());
        $this->assertFalse($permanentConfig->isExpired());
    }
}
