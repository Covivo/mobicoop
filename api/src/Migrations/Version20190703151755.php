<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190703151755 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ask_history (id INT AUTO_INCREMENT NOT NULL, ask_id INT DEFAULT NULL, status SMALLINT NOT NULL, type SMALLINT NOT NULL, created_date DATETIME NOT NULL, INDEX IDX_F4597A9B93F8B63 (ask_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ask_history ADD CONSTRAINT FK_F4597A9B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FB93F8B63');
        $this->addSql('DROP INDEX IDX_B6BD307FB93F8B63 ON message');
        $this->addSql('ALTER TABLE message CHANGE ask_id ask_history_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F885E0A12 FOREIGN KEY (ask_history_id) REFERENCES ask_history (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6BD307F885E0A12 ON message (ask_history_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F885E0A12');
        $this->addSql('DROP TABLE ask_history');
        $this->addSql('DROP INDEX UNIQ_B6BD307F885E0A12 ON message');
        $this->addSql('ALTER TABLE message CHANGE ask_history_id ask_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FB93F8B63 ON message (ask_id)');
    }
}
