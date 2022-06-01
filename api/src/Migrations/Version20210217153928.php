<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210217153928 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEB93F8B63');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEB93F8B63');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
    }
}
