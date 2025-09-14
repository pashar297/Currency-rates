<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CurrencyPair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CurrencyPairRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyPair::class);
    }

    public function findActivePairsGroupedBySource(): array
    {
        $pairs = $this->createQueryBuilder('cp')
            ->where('cp.isActive = true')
            ->andWhere('cp.source IS NOT NULL')
            ->getQuery()
            ->getResult();

        $grouped = [];
        foreach ($pairs as $pair) {
            $grouped[$pair->getSource()][] = $pair;
        }

        return $grouped;
    }
}
