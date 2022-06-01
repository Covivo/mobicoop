<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Custom Migration to fix falsy OneToOne link between Criteria and Direction !
 */
final class Version20200422101010 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // revert migration Version20200417095843 for those who passed it !
        $this->addSql('ALTER TABLE criteria DROP INDEX IF EXISTS UNIQ_B61F9B81A862FD7E, ADD INDEX IF NOT EXISTS IDX_B61F9B81A862FD7E (direction_driver_id)');
        $this->addSql('ALTER TABLE criteria DROP INDEX IF EXISTS UNIQ_B61F9B818044A959, ADD INDEX IF NOT EXISTS IDX_B61F9B818044A959 (direction_passenger_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
