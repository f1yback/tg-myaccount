<?php

declare(strict_types=1);

namespace App\Service\Vpn;

interface VpnApiClientInterface
{
    public function createWireGuardConfig(int $userId): string;

    public function createVlessRealityConfig(int $userId): string;

    public function deleteConfig(string $configId): bool;

    /** @return array<array-key, mixed>|null */
    public function getConfigStatus(string $configId): ?array;
}
