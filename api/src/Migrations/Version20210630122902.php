<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210630122902 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria CHANGE driver_price driver_price NUMERIC(10, 6) DEFAULT NULL, CHANGE driver_computed_price driver_computed_price NUMERIC(10, 6) DEFAULT NULL, CHANGE driver_computed_rounded_price driver_computed_rounded_price NUMERIC(10, 6) DEFAULT NULL, CHANGE passenger_price passenger_price NUMERIC(10, 6) DEFAULT NULL, CHANGE passenger_computed_price passenger_computed_price NUMERIC(10, 6) DEFAULT NULL, CHANGE passenger_computed_rounded_price passenger_computed_rounded_price NUMERIC(10, 6) DEFAULT NULL, CHANGE price_km price_km NUMERIC(10, 6) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria CHANGE driver_price driver_price NUMERIC(6, 2) DEFAULT NULL, CHANGE driver_computed_price driver_computed_price NUMERIC(6, 2) DEFAULT NULL, CHANGE driver_computed_rounded_price driver_computed_rounded_price NUMERIC(6, 2) DEFAULT NULL, CHANGE passenger_price passenger_price NUMERIC(6, 2) DEFAULT NULL, CHANGE passenger_computed_price passenger_computed_price NUMERIC(6, 2) DEFAULT NULL, CHANGE passenger_computed_rounded_price passenger_computed_rounded_price NUMERIC(6, 2) DEFAULT NULL, CHANGE price_km price_km NUMERIC(4, 2) DEFAULT NULL');
    }
}
