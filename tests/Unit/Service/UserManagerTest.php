<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\User\UserRepositoryInterface;
use App\Service\User\UserManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserManagerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private LoggerInterface&MockObject $logger;
    private UserManager $userManager;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userManager = new UserManager($this->userRepository, $this->logger);
    }

    public function testFindOrCreateUserCreatesNewUser(): void
    {
        $telegramId = 123456789;

        $this->userRepository
            ->expects($this->once())
            ->method('findByTelegramId')
            ->with($telegramId)
            ->willReturn(null);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $user = $this->userManager->findOrCreateUser($telegramId, null, null, null);

        $this->assertEquals($telegramId, $user->getTelegramId());
        $this->assertNull($user->getUsername());
        $this->assertNull($user->getFirstName());
        $this->assertNull($user->getLastName());
    }

    public function testFindOrCreateUserReturnsExistingUser(): void
    {
        $telegramId = 123456789;
        $existingUser = new User($telegramId);

        $this->userRepository
            ->expects($this->once())
            ->method('findByTelegramId')
            ->with($telegramId)
            ->willReturn($existingUser);

        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $user = $this->userManager->findOrCreateUser($telegramId, null, null, null);

        $this->assertSame($existingUser, $user);
    }

    public function testFindOrCreateUserUpdatesExistingUserProfile(): void
    {
        $telegramId = 123456789;
        $existingUser = new User($telegramId);
        $username = 'testuser';
        $firstName = 'John';
        $lastName = 'Doe';

        $this->userRepository
            ->expects($this->once())
            ->method('findByTelegramId')
            ->with($telegramId)
            ->willReturn($existingUser);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($existingUser);

        $user = $this->userManager->findOrCreateUser($telegramId, $username, $firstName, $lastName);

        $this->assertSame($existingUser, $user);
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($lastName, $user->getLastName());
    }

    public function testCanGetFreeTrial(): void
    {
        $user = new User(123);

        // For a new user, isNewUser should return true
        $this->assertTrue($this->userManager->canGetFreeTrial($user));
    }

    /**
     * @dataProvider balanceOperationProvider
     */
    public function testBalanceOperations(string $initialBalance, string $amount, string $operation, string $expectedBalance): void
    {
        $user = new User(123);
        $user->setBalance($initialBalance);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        if ('add' === $operation) {
            $this->userManager->addBalance($user, $amount);
        } else {
            $this->userManager->subtractBalance($user, $amount);
        }

        $this->assertEquals($expectedBalance, $user->getBalance());
    }

    public static function balanceOperationProvider(): array
    {
        return [
            'add balance' => ['100.00', '50.00', 'add', '150.00'],
            'subtract balance' => ['100.00', '30.00', 'subtract', '70.00'],
            'add zero' => ['100.00', '0.00', 'add', '100.00'],
            'subtract zero' => ['100.00', '0.00', 'subtract', '100.00'],
        ];
    }
}
