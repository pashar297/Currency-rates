<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

abstract class BaseExchangeRateRequest
{
    #[Assert\NotBlank(message: 'Parameter "pair" is required')]
    #[Assert\Regex(
        pattern: '/^[A-Z]{2,10}\/[A-Z]{2,10}$/',
        message: 'Invalid pair format. Use format: USD/EUR'
    )]
    public string $pair;

    public function getFromCurrency(): string
    {
        return explode('/', $this->pair)[0];
    }

    public function getToCurrency(): string
    {
        return explode('/', $this->pair)[1];
    }
}
