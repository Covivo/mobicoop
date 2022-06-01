<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190703152601 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ask_history ADD message_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ask_history ADD CONSTRAINT FK_F4597A9537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F4597A9537A1329 ON ask_history (message_id)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F885E0A12');
        $this->addSql('DROP INDEX UNIQ_B6BD307F885E0A12 ON message');
        $this->addSql('ALTER TABLE message DROP ask_history_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ask_history DROP FOREIGN KEY FK_F4597A9537A1329');
        $this->addSql('DROP INDEX UNIQ_F4597A9537A1329 ON ask_history');
        $this->addSql('ALTER TABLE ask_history DROP message_id');
        $this->addSql('ALTER TABLE message ADD ask_history_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F885E0A12 FOREIGN KEY (ask_history_id) REFERENCES ask_history (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6BD307F885E0A12 ON message (ask_history_id)');
    }
}
