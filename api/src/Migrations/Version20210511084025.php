<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210511084025 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_person ADD proposal_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_75908575F4792058 ON mass_person (proposal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_person DROP FOREIGN KEY FK_75908575F4792058');
        $this->addSql('DROP INDEX UNIQ_75908575F4792058 ON mass_person');
        $this->addSql('ALTER TABLE mass_person DROP proposal_id');
    }
}
