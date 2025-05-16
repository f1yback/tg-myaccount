<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface
{
    public function findByTelegramId(int $telegramId): ?User;

    public function findById(Uuid $id): ?User;

    public function save(User $user): void;

    public function remove(User $user): void;
}
