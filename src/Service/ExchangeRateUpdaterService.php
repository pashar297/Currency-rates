<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ExchangeRate;
use App\Repository\CurrencyPairRepository;
use App\Service\Provider\ExchangeRateProviderInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class ExchangeRateUpdaterService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CurrencyPairRepository $pairRepository,
        private readonly LoggerInterface $logger,
        private readonly iterable $providers
    ) {}

    public function updateRates(): array
    {
        $results = [];
        $pairsBySource = $this->pairRepository->findActivePairsGroupedBySource();

        foreach ($this->providers as $provider) {
            $results[$provider->getName()] = $this->processProvider($provider, $pairsBySource);
        }

        return $results;
    }

    private function processProvider(ExchangeRateProviderInterface $provider, array $pairsBySource): array
    {
        $providerName = $provider->getName();
        $pairs = $pairsBySource[$providerName] ?? [];

        if (empty($pairs)) {
            $this->logger->info("No pairs configured for provider {$providerName}");
            return ['success' => true, 'rates_saved' => 0];
        }

        if (!$provider->isAvailable()) {
            $this->logger->warning("Provider {$providerName} is not available");
            return ['success' => false, 'error' => 'Provider not available'];
        }

        try {
            $rates = $provider->fetchRatesForPairs($pairs);
            $saved = $this->saveRates($rates, $providerName, $pairs);

            $this->logger->info("Updated {$saved} rates from {$providerName}");
            return ['success' => true, 'rates_saved' => $saved];
        } catch (Throwable $e) {
            $this->logger->error("Failed to update rates from {$providerName}: {$e->getMessage()}");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function saveRates(array $rates, string $providerName, array $pairs): int
    {
        $pairsById = $this->indexPairsById($pairs);
        $saved     = 0;
        $timestamp = new DateTimeImmutable();

        foreach ($rates as $pairId => $rate) {
            if (isset($pairsById[$pairId])) {
                $this->persistExchangeRate($pairsById[$pairId], $rate, $timestamp, $providerName);
                $saved++;
            }
        }

        $this->em->flush();
        return $saved;
    }

    private function indexPairsById(array $pairs): array
    {
        $pairsById = [];
        foreach ($pairs as $pair) {
            $pairsById[$pair->getId()] = $pair;
        }

        return $pairsById;
    }

    private function persistExchangeRate($pair, $rate, DateTimeImmutable $timestamp, string $provider): void
    {
        $exchangeRate = new ExchangeRate();
        $exchangeRate
            ->setCurrencyPair($pair)
            ->setRate($rate)
            ->setTimestamp($timestamp)
            ->setProvider($provider);

        $this->em->persist($exchangeRate);
    }
}
