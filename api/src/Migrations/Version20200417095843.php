<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200417095843 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Migration aborted ! we let the code as some instances may have already passed it
        // fix is in Version20200422101010.php
        // $this->addSql('ALTER TABLE criteria DROP INDEX IDX_B61F9B81A862FD7E, ADD UNIQUE INDEX UNIQ_B61F9B81A862FD7E (direction_driver_id)');
        // $this->addSql('ALTER TABLE criteria DROP INDEX IDX_B61F9B818044A959, ADD UNIQUE INDEX UNIQ_B61F9B818044A959 (direction_passenger_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // $this->addSql('ALTER TABLE criteria DROP INDEX UNIQ_B61F9B81A862FD7E, ADD INDEX IDX_B61F9B81A862FD7E (direction_driver_id)');
        // $this->addSql('ALTER TABLE criteria DROP INDEX UNIQ_B61F9B818044A959, ADD INDEX IDX_B61F9B818044A959 (direction_passenger_id)');
    }
}
