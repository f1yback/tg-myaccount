<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @property EntityManagerInterface $_em
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByTelegramId(int $telegramId): ?User
    {
        return $this->findOneBy(['telegramId' => $telegramId]);
    }

    public function save(User $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function remove(User $user): void
    {
        $this->_em->remove($user);
        $this->_em->flush();
    }

    public function findById(Uuid $id): ?User
    {
        return $this->find($id);
    }
}
