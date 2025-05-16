<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\User\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

class UserManager implements UserManagerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function findOrCreateUser(int $telegramId, ?string $username, ?string $firstName, ?string $lastName): User
    {
        $this->logger->debug('UserManager::findOrCreateUser - Looking for user', [
            'telegram_id' => $telegramId,
        ]);

        $user = $this->userRepository->findByTelegramId($telegramId);
        $needsSave = false;

        if (null === $user) {
            $this->logger->info('UserManager::findOrCreateUser - Creating new user', [
                'telegram_id' => $telegramId,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
            $user = new User($telegramId);
            $needsSave = true;
        }

        if ($this->profileNeedsUpdate($user, $username, $firstName, $lastName)) {
            $this->logger->info('UserManager::findOrCreateUser - Updating user profile', [
                'telegram_id' => $telegramId,
                'old_username' => $user->getUsername(),
                'new_username' => $username,
            ]);
            $this->updateUserProfile($user, $username, $firstName, $lastName);
            $needsSave = true;
        }

        if ($needsSave) {
            $this->userRepository->save($user);
            $this->logger->info('UserManager::findOrCreateUser - User saved', [
                'telegram_id' => $telegramId,
                'user_id' => $user->getId()->toString(),
            ]);
        } else {
            $this->logger->debug('UserManager::findOrCreateUser - User found, no changes needed', [
                'telegram_id' => $telegramId,
            ]);
        }

        return $user;
    }

    private function profileNeedsUpdate(User $user, ?string $username, ?string $firstName, ?string $lastName): bool
    {
        return $user->getUsername() !== $username
            || $user->getFirstName() !== $firstName
            || $user->getLastName() !== $lastName;
    }

    public function updateUserProfile(User $user, ?string $username, ?string $firstName, ?string $lastName): void
    {
        $user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
    }

    public function addBalance(User $user, string $amount): void
    {
        $user->addToBalance($amount);
        $this->userRepository->save($user);
    }

    public function subtractBalance(User $user, string $amount): void
    {
        $user->subtractFromBalance($amount);
        $this->userRepository->save($user);
    }

    public function canGetFreeTrial(User $user): bool
    {
        $result = $user->isNewUser();
        $this->logger->debug('UserManager::canGetFreeTrial - Checking if user can get free trial', [
            'user_id' => $user->getTelegramId(),
            'can_get_trial' => $result,
        ]);

        return $result;
    }
}
