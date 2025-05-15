<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\User\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'bigint', unique: true)]
    private int $telegramId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $balance;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(int $telegramId)
    {
        $this->id = Uuid::v7();
        $this->telegramId = $telegramId;
        $this->balance = '0.00';
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTelegramId(): int
    {
        return $this->telegramId;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): self
    {
        $this->balance = $balance;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function addToBalance(string $amount): self
    {
        $currentBalance = (float) $this->balance;
        $addAmount = (float) $amount;
        $newBalance = $currentBalance + $addAmount;

        $this->balance = number_format($newBalance, 2, '.', '');
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function subtractFromBalance(string $amount): self
    {
        $currentBalance = (float) $this->balance;
        $subtractAmount = (float) $amount;
        $newBalance = $currentBalance - $subtractAmount;

        if ($newBalance < 0) {
            throw new \DomainException('Insufficient balance');
        }

        $this->balance = number_format($newBalance, 2, '.', '');
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isNewUser(): bool
    {
        return 0 === $this->createdAt->diff(new \DateTimeImmutable())->days;
    }
}
