<?php

declare(strict_types=1);

namespace App\Service\Vpn;

use App\Entity\User;
use App\Entity\VpnConfig;
use App\Entity\VpnProtocol;
use App\Exception\Vpn\FreeTrialNotEligibleException;
use App\Exception\Vpn\VpnConfigCreationException;

interface VpnConfigManagerInterface
{
    /**
     * @throws FreeTrialNotEligibleException
     * @throws VpnConfigCreationException
     */
    public function createFreeTrialConfig(User $user, VpnProtocol $protocol): VpnConfig;

    /** @return array<VpnConfig> */
    public function getActiveConfigs(User $user): array;

    public function downloadConfig(VpnConfig $config): string;

    public function expireOldConfigs(): int;

    public function revokeConfig(VpnConfig $config): void;
}
