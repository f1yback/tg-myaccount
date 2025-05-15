<?php

declare(strict_types=1);

namespace App\Entity;

enum VpnStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';

    public function isActive(): bool
    {
        return self::ACTIVE === $this;
    }

    public function canBeUsed(): bool
    {
        return self::ACTIVE === $this;
    }
}
