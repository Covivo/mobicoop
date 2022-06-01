<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190705141257 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sent_by');
        $this->addSql('ALTER TABLE recipient ADD sent_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sent_by (id INT AUTO_INCREMENT NOT NULL, message_id INT DEFAULT NULL, medium_id INT DEFAULT NULL, sent_date DATETIME DEFAULT NULL, INDEX IDX_C378DCF6537A1329 (message_id), INDEX IDX_C378DCF6E252B6A5 (medium_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sent_by ADD CONSTRAINT FK_C378DCF6537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE sent_by ADD CONSTRAINT FK_C378DCF6E252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
        $this->addSql('ALTER TABLE recipient DROP sent_date');
    }
}
