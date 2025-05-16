<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;

interface UserManagerInterface
{
    public function findOrCreateUser(int $telegramId, ?string $username, ?string $firstName, ?string $lastName): User;

    public function updateUserProfile(User $user, ?string $username, ?string $firstName, ?string $lastName): void;

    public function addBalance(User $user, string $amount): void;

    public function subtractBalance(User $user, string $amount): void;

    public function canGetFreeTrial(User $user): bool;
}
