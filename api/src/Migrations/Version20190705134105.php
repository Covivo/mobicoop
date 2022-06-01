<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190705134105 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sent_by CHANGE sent_date sent_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notified CHANGE sent_date sent_date DATETIME DEFAULT NULL, CHANGE received_date received_date DATETIME DEFAULT NULL, CHANGE read_date read_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE recipient CHANGE read_date read_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE notified CHANGE sent_date sent_date DATETIME NOT NULL, CHANGE received_date received_date DATETIME NOT NULL, CHANGE read_date read_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE recipient CHANGE read_date read_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE sent_by CHANGE sent_date sent_date DATETIME NOT NULL');
    }
}
