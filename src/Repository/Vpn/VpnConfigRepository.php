<?php

declare(strict_types=1);

namespace App\Repository\Vpn;

use App\Entity\User;
use App\Entity\VpnConfig;
use App\Entity\VpnStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<VpnConfig>
 *
 * @property \Doctrine\ORM\EntityManagerInterface $_em
 */
class VpnConfigRepository extends ServiceEntityRepository implements VpnConfigRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpnConfig::class);
    }

    /** @return array<VpnConfig> */
    public function findActiveByUser(User $user): array
    {
        return $this->findBy([
            'user' => $user,
            'status' => VpnStatus::ACTIVE,
        ]);
    }

    public function findById(Uuid $id): ?VpnConfig
    {
        return $this->find($id);
    }

    public function save(VpnConfig $vpnConfig): void
    {
        $this->_em->persist($vpnConfig);
        $this->_em->flush();
    }

    public function remove(VpnConfig $vpnConfig): void
    {
        $this->_em->remove($vpnConfig);
        $this->_em->flush();
    }

    /** @return array<VpnConfig> */
    public function findExpiredConfigs(): array
    {
        $qb = $this->createQueryBuilder('vc');
        $qb->where('vc.status = :status')
           ->andWhere('vc.expiresAt IS NOT NULL')
           ->andWhere('vc.expiresAt < :now')
           ->setParameter('status', VpnStatus::ACTIVE)
           ->setParameter('now', new \DateTimeImmutable());

        return $qb->getQuery()->getResult();
    }
}
