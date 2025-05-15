<?php

declare(strict_types=1);

namespace App\Entity;

enum VpnProtocol: string
{
    case WIREGUARD = 'wireguard';
    case VLESS_REALITY = 'vless_reality';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::WIREGUARD => 'WireGuard',
            self::VLESS_REALITY => 'VLESS + Reality',
        };
    }
}
