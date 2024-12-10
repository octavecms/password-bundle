<?php

namespace Octave\PasswordBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Octave\PasswordBundle\Entity\PasswordHistory;

class PasswordHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordHistory::class);
    }

    /**
     * @param object $user
     * @param int $limit
     * @return PasswordHistory[]
     */
    public function getPasswordHistory(object $user, int $limit): array
    {
        return $this->createQueryBuilder('ph')
            ->andWhere('ph.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ph.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
