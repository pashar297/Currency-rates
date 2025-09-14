<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\Table(name: 'exchange_rates')]
#[ORM\Index(name: 'idx_pair_timestamp', columns: ['currency_pair_id', 'timestamp'])]
class ExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CurrencyPair::class, inversedBy: 'exchangeRates')]
    #[ORM\JoinColumn(nullable: false)]
    private CurrencyPair $currencyPair;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 8)]
    private float $rate;

    #[ORM\Column]
    private DateTimeImmutable $timestamp;

    #[ORM\Column(length: 50)]
    private string $provider;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrencyPair(): CurrencyPair
    {
        return $this->currencyPair;
    }

    public function setCurrencyPair(CurrencyPair $currencyPair): self
    {
        $this->currencyPair = $currencyPair;
        return $this;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    public function getTimestamp(): ?DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeImmutable $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }
}
