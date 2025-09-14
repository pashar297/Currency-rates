<?php

declare(strict_types=1);

namespace App\Service\Provider;

use App\Exception\ProviderException;
use Throwable;

final class BinanceProvider extends AbstractExchangeRateProvider
{
    private const string API_GET_TICKER_PRICE_ENDPOINT = '/api/v3/ticker/price';

    private const string API_PING_ENDPOINT = '/api/v3/ping';

    private const string BASE_CURRENCY = 'USDT';

    private const int PING_TIMEOUT = 5;

    public function __construct(
        protected string $apiUrl,
        protected int $timeout = 10
    ) {
        parent::__construct($apiUrl, $timeout);
    }

    public function getName(): string
    {
        return 'binance';
    }

    public function fetchRatesForPairs(array $currencyPairs): array
    {
        if (empty($currencyPairs)) {
            return [];
        }

        try {
            $usdtPairs = $this->buildUsdtPairs($currencyPairs);
            $usdtRates = $this->makeRequest(self::API_GET_TICKER_PRICE_ENDPOINT, [
                'query' => ['symbols' => json_encode($usdtPairs)]
            ]);

            return $this->calculateCrossRates($usdtRates, $currencyPairs);
        } catch (Throwable $e) {
            throw new ProviderException($e->getMessage(), 0, $e);
        }
    }

    public function isAvailable(): bool
    {
        try {
            $this->makeRequest(self::API_PING_ENDPOINT, [
                'timeout' => self::PING_TIMEOUT
            ]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function buildUsdtPairs(array $currencyPairs): array
    {
        $currencies = [];
        foreach ($currencyPairs as $pair) {
            $currencies[] = $pair->getBaseCurrency()->getCode();
            $currencies[] = $pair->getQuoteCurrency()->getCode();
        }

        $usdtPairs = [];
        foreach (array_unique($currencies) as $currency) {
            if ($currency !== self::BASE_CURRENCY) {
                $usdtPairs[] = $currency . self::BASE_CURRENCY;
            }
        }

        return $usdtPairs;
    }

    private function calculateCrossRates(array $usdtRates, array $currencyPairs): array
    {
        $usdtPrices = [self::BASE_CURRENCY => 1.0];

        foreach ($usdtRates as $rate) {
            if (isset($rate['symbol'], $rate['price']) && is_numeric($rate['price'])) {
                $currency = str_replace(self::BASE_CURRENCY, '', $rate['symbol']);
                $usdtPrices[$currency] = (float) $rate['price'];
            }
        }

        $rates = [];
        foreach ($currencyPairs as $pair) {
            $base = $pair->getBaseCurrency()->getCode();
            $quote = $pair->getQuoteCurrency()->getCode();

            if (isset($usdtPrices[$base], $usdtPrices[$quote]) && $usdtPrices[$quote] != 0) {
                $rates[$pair->getId()] = $usdtPrices[$base] / $usdtPrices[$quote];
            }
        }

        return $rates;
    }
}
