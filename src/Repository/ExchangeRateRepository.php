<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;

final class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    public function findRatesForPeriod(
        string $baseCurrency,
        string $quoteCurrency,
        DateTimeImmutable $from,
        DateTimeImmutable $to = null
    ): array {
        $qb = $this->createBaseQueryBuilder($baseCurrency, $quoteCurrency);

        if ($to === null) {
            $qb->andWhere('er.timestamp >= :from')
                ->setParameter('from', $from);
        } else {
            $qb->andWhere('er.timestamp BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->getQuery()->getResult();
    }

    public function findRatesForDay(string $baseCurrency, string $quoteCurrency, DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay   = $date->setTime(23, 59, 59);

        return $this->createBaseQueryBuilder($baseCurrency, $quoteCurrency)
            ->andWhere('er.timestamp BETWEEN :start AND :end')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getResult();
    }

    private function createBaseQueryBuilder(string $baseCurrency, string $quoteCurrency): QueryBuilder
    {
        return $this->createQueryBuilder('er')
            ->join('er.currencyPair', 'cp')
            ->join('cp.baseCurrency', 'bc')
            ->join('cp.quoteCurrency', 'qc')
            ->where('bc.code = :baseCurrency')
            ->andWhere('qc.code = :quoteCurrency')
            ->setParameter('baseCurrency', $baseCurrency)
            ->setParameter('quoteCurrency', $quoteCurrency)
            ->orderBy('er.timestamp', 'ASC');
    }
}
