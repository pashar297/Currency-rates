<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'currency_pairs')]
#[ORM\UniqueConstraint(name: 'unique_pair', columns: ['base_currency_id', 'quote_currency_id'])]
class CurrencyPair
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Currency::class, inversedBy: 'basePairs')]
    #[ORM\JoinColumn(name: 'base_currency_id', referencedColumnName: 'id', nullable: false)]
    private Currency $baseCurrency;

    #[ORM\ManyToOne(targetEntity: Currency::class, inversedBy: 'quotePairs')]
    #[ORM\JoinColumn(name: 'quote_currency_id', referencedColumnName: 'id', nullable: false)]
    private Currency $quoteCurrency;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isActive = false;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $source = null;

    #[ORM\OneToMany(targetEntity: ExchangeRate::class, mappedBy: 'currencyPair', cascade: ['persist'])]
    private Collection $exchangeRates;

    public function __construct()
    {
        $this->exchangeRates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseCurrency(): Currency
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(Currency $baseCurrency): self
    {
        $this->baseCurrency = $baseCurrency;
        return $this;
    }

    public function getQuoteCurrency(): Currency
    {
        return $this->quoteCurrency;
    }

    public function setQuoteCurrency(Currency $quoteCurrency): self
    {
        $this->quoteCurrency = $quoteCurrency;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getExchangeRates(): Collection
    {
        return $this->exchangeRates;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;
        return $this;
    }
}
