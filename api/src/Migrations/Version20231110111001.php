<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231110111001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD version VARCHAR(50) DEFAULT NULL COMMENT \'The subscription version. Could be CoupPouceCEE2023 or CEEStandardMobicoop\'');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD version VARCHAR(50) DEFAULT NULL COMMENT \'The subscription version. Could be CoupPouceCEE2023 or CEEStandardMobicoop\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription DROP version');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription DROP version');
    }
}
