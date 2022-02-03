<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110101947 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE identity_proof ADD admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE identity_proof ADD CONSTRAINT FK_11A20A2D642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_11A20A2D642B8210 ON identity_proof (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE identity_proof DROP FOREIGN KEY FK_11A20A2D642B8210');
        $this->addSql('DROP INDEX IDX_11A20A2D642B8210 ON identity_proof');
        $this->addSql('ALTER TABLE identity_proof DROP admin_id');
    }
}
