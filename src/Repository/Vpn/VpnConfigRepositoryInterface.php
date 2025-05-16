<?php

declare(strict_types=1);

namespace App\Repository\Vpn;

use App\Entity\User;
use App\Entity\VpnConfig;
use Symfony\Component\Uid\Uuid;

interface VpnConfigRepositoryInterface
{
    /** @return array<VpnConfig> */
    public function findActiveByUser(User $user): array;

    public function findById(Uuid $id): ?VpnConfig;

    public function save(VpnConfig $vpnConfig): void;

    public function remove(VpnConfig $vpnConfig): void;

    /** @return array<VpnConfig> */
    public function findExpiredConfigs(): array;
}
