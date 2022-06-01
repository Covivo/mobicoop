<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200514142759 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof ADD ask_id INT DEFAULT NULL, ADD type VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('CREATE INDEX IDX_59B969CEB93F8B63 ON carpool_proof (ask_id)');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0FBF2A5E5');
        $this->addSql('DROP INDEX UNIQ_6826EAE0FBF2A5E5 ON ask');
        $this->addSql('ALTER TABLE ask DROP carpool_proof_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ask ADD carpool_proof_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0FBF2A5E5 FOREIGN KEY (carpool_proof_id) REFERENCES carpool_proof (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6826EAE0FBF2A5E5 ON ask (carpool_proof_id)');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEB93F8B63');
        $this->addSql('DROP INDEX IDX_59B969CEB93F8B63 ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof DROP ask_id, DROP type');
    }
}
