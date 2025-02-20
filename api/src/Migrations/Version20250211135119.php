<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211135119 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE journey ADD gender SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE journey ADD seats_driver INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE journey ADD price_km NUMERIC(10, 6) DEFAULT \'0\', ADD distance INT DEFAULT 0 NOT NULL, ADD duration INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE journey DROP price_km, DROP distance, DROP duration');
        $this->addSql('ALTER TABLE journey DROP seats_driver');
        $this->addSql('ALTER TABLE journey DROP gender');
    }
}
