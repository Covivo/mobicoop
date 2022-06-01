<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191118084315 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE04F05FAE');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE04F05FAE FOREIGN KEY (ask_linked_id) REFERENCES ask (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE04F05FAE');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE04F05FAE FOREIGN KEY (ask_linked_id) REFERENCES ask (id)');
    }
}
