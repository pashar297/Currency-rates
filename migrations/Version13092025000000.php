<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version13092025000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add basic currencies and currency pairs with Binance source';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO currencies (code, name) VALUES
            ('EUR', 'Euro'),
            ('BTC', 'Bitcoin'),
            ('ETH', 'Ethereum'),
            ('LTC', 'Litecoin')
        ");

        $this->addSql("INSERT INTO currency_pairs (base_currency_id, quote_currency_id, is_active, source)
            SELECT eur.id as base_currency_id,
                   crypto.id as quote_currency_id,
                   true as is_active,
                   'binance' as source
              FROM currencies eur
        CROSS JOIN currencies crypto
             WHERE eur.code = 'EUR'
               AND crypto.code IN ('BTC', 'ETH', 'LTC')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM currency_pairs WHERE source = 'binance'");

        $this->addSql("DELETE FROM currencies WHERE code IN ('EUR', 'BTC', 'ETH', 'LTC')");
    }
}
