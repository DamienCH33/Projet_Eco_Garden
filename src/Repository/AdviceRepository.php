<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Advice>
 */
class AdviceRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }

    /**
     * @return Advice[]
     */
    public function findByMonth(int $month): array
    {
        return $this->createQueryBuilder('a')
            ->where('JSON_CONTAINS(a.month, :month) = 1')
            ->setParameter('month', json_encode($month))
            ->getQuery()
            ->getResult();
    }
}
