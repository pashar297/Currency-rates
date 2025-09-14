<?php

declare(strict_types=1);

namespace App\Service\Provider;

interface ExchangeRateProviderInterface
{
    public function getName(): string;

    public function fetchRatesForPairs(array $currencyPairs): array;

    public function isAvailable(): bool;
}
