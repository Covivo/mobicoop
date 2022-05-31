<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190709090039 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4B93F8B63');
        $this->addSql('DROP INDEX IDX_D23269D4B93F8B63 ON notified');
        $this->addSql('ALTER TABLE notified CHANGE ask_id ask_history_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4885E0A12 FOREIGN KEY (ask_history_id) REFERENCES ask_history (id)');
        $this->addSql('CREATE INDEX IDX_D23269D4885E0A12 ON notified (ask_history_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE notified CHANGE ask_history_id ask_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('CREATE INDEX IDX_D23269D4B93F8B63 ON notified (ask_id)');
    }
}
