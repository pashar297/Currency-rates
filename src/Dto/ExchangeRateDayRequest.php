<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ExchangeRateDayRequest extends BaseExchangeRateRequest
{
    #[Assert\NotBlank(message: 'Parameter "date" is required')]
    #[Assert\Date(message: 'Invalid date format. Use YYYY-MM-DD')]
    public string $date;
}