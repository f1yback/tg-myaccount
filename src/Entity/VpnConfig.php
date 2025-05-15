<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Vpn\VpnConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: VpnConfigRepository::class)]
#[ORM\Table(name: 'vpn_configs')]
class VpnConfig
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 20, enumType: VpnProtocol::class)]
    private VpnProtocol $protocol;

    #[ORM\Column(type: 'string', length: 20, enumType: VpnStatus::class)]
    private VpnStatus $status;

    #[ORM\Column(type: 'text')]
    private string $configData;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(User $user, VpnProtocol $protocol, string $configData, ?\DateTimeImmutable $expiresAt = null)
    {
        $this->id = Uuid::v7();
        $this->user = $user;
        $this->protocol = $protocol;
        $this->status = VpnStatus::ACTIVE;
        $this->configData = $configData;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getProtocol(): VpnProtocol
    {
        return $this->protocol;
    }

    public function getStatus(): VpnStatus
    {
        return $this->status;
    }

    public function setStatus(VpnStatus $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getConfigData(): string
    {
        return $this->configData;
    }

    public function setConfigData(string $configData): self
    {
        $this->configData = $configData;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isExpired(): bool
    {
        if (null === $this->expiresAt) {
            return false;
        }

        return new \DateTimeImmutable() > $this->expiresAt;
    }

    public function expire(): self
    {
        $this->status = VpnStatus::EXPIRED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function revoke(): self
    {
        $this->status = VpnStatus::REVOKED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
