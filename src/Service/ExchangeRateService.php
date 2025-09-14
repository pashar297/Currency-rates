<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ExchangeRateRepository;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ExchangeRateService
{
    public function __construct(
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly CacheInterface $cache,
        #[Autowire('%app.get_rates.cache.short_ttl%')]
        private readonly int $shortCacheTtl,
        #[Autowire('%app.get_rates.cache.long_ttl%')]
        private readonly int $longCacheTtl
    ) {
    }

    public function getRatesForLast24Hours(string $fromCurrency, string $toCurrency): array
    {
        $fromDateTime = new DateTimeImmutable('-24 hours');
        $rates = $this->exchangeRateRepository->findRatesForPeriod(
            $fromCurrency,
            $toCurrency,
            $fromDateTime
        );

        return $this->formatRates($rates);
    }

    public function getRatesForDay(string $fromCurrency, string $toCurrency, DateTimeImmutable $date): array
    {
        $cacheKey = sprintf('rates.day.%s.%s.%s', $fromCurrency, $toCurrency, $date->format('Y-m-d'));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($fromCurrency, $toCurrency, $date) {
            $ttl = $this->determineCacheTtl($date);
            $item->expiresAfter($ttl);

            $rates = $this->exchangeRateRepository->findRatesForDay(
                $fromCurrency,
                $toCurrency,
                $date
            );

            return $this->formatRates($rates);
        });
    }

    private function determineCacheTtl(DateTimeImmutable $date): int
    {
        $isToday = $date->format('Y-m-d') === date('Y-m-d');
        return $isToday ? $this->shortCacheTtl : $this->longCacheTtl;
    }

    private function formatRates(array $rates): array
    {
        return array_map(fn($rate) => [
            'timestamp' => $rate->getTimestamp()->format('c'),
            'rate'      => $rate->getRate(),
        ], $rates);
    }
}