<?php

declare(strict_types=1);

namespace App\Service\Provider;

use App\Exception\ProviderException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Throwable;

abstract class AbstractExchangeRateProvider implements ExchangeRateProviderInterface
{
    protected const int TIMEOUT = 10;

    protected const array DEFAULT_HEADERS = ['Content-Type' => 'application/json'];

    protected ?Client $httpClient = null;

    public function __construct(
        protected string $apiUrl = '',
        protected int $timeout = self::TIMEOUT
    ) {}

    protected function makeRequest(string $endpoint, array $options = []): array
    {
        try {
            $client = $this->getHttpClient();

            $defaultOptions = [
                'http_errors'    => false,
                'decode_content' => false
            ];

            $response = $client->get($endpoint, array_merge($defaultOptions, $options));
            $content = $response->getBody()->getContents();

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ProviderException('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new ProviderException($e->getMessage(), 0, $e);
        } catch (Throwable $e) {
            throw new ProviderException('Unexpected error occurred: ' . $e->getMessage(), 0, $e);
        }
    }

    protected function getHttpClient(): Client
    {
        if (!$this->httpClient) {
            $this->httpClient = new Client([
                'base_uri' => $this->apiUrl,
                'timeout'  => $this->timeout,
                'headers'  => $this->getHeaders()
            ]);
        }

        return $this->httpClient;
    }

    protected function getHeaders(): array
    {
        return static::DEFAULT_HEADERS;
    }
}