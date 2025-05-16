<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $telegramId = 123456789;
        $user = new User($telegramId);

        $this->assertEquals($telegramId, $user->getTelegramId());
        $this->assertEquals('0.00', $user->getBalance());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testUserProfileUpdate(): void
    {
        $user = new User(123456789);

        $user->setUsername('testuser');
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
    }

    public function testBalanceOperations(): void
    {
        $user = new User(123456789);

        $user->addToBalance('10.50');
        $this->assertEquals('10.50', $user->getBalance());

        $user->addToBalance('5.25');
        $this->assertEquals('15.75', $user->getBalance());

        $user->subtractFromBalance('5.00');
        $this->assertEquals('10.75', $user->getBalance());
    }

    public function testSubtractBalanceInsufficientFunds(): void
    {
        $user = new User(123456789);
        $user->addToBalance('5.00');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient balance');

        $user->subtractFromBalance('10.00');
    }

    public function testIsNewUser(): void
    {
        $user = new User(123456789);
        $this->assertTrue($user->isNewUser());
    }
}
