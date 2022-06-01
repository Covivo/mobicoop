<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200304145914 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE position DROP INDEX IDX_462CE4F5F4792058, ADD UNIQUE INDEX UNIQ_462CE4F5F4792058 (proposal_id)');
        $this->addSql('ALTER TABLE position CHANGE proposal_id proposal_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE position DROP INDEX UNIQ_462CE4F5F4792058, ADD INDEX IDX_462CE4F5F4792058 (proposal_id)');
        $this->addSql('ALTER TABLE position CHANGE proposal_id proposal_id INT DEFAULT NULL');
    }
}
