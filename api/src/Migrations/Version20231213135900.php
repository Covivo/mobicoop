<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231213135900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE gratuity_notification DROP notified_date');

        $this->addSql('UPDATE mobconnect__long_distance_subscription SET version = 0');
        $this->addSql('UPDATE mobconnect__short_distance_subscription SET version = 0');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription CHANGE version version SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription CHANGE version version SMALLINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE gratuity_notification ADD notified_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription CHANGE version version SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription CHANGE version version SMALLINT DEFAULT NULL');
    }
}
