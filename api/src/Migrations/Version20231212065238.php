<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231212065238 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription DROP version_status');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription DROP version_status');

        $this->addSql('UPDATE mobconnect__long_distance_subscription SET version = 0 WHERE version=\'CoupPouceCEE2023\'');
        $this->addSql('UPDATE mobconnect__short_distance_subscription SET version = 0 WHERE version=\'CoupPouceCEE2023\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD version_status SMALLINT DEFAULT NULL COMMENT \'The subscription version status.\'');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD version_status SMALLINT DEFAULT NULL COMMENT \'The subscription version status.\'');
    }
}
