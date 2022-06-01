<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200505091027 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass ADD community_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mass ADD CONSTRAINT FK_6C035B66FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6C035B66FDA7B0BF ON mass (community_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass DROP FOREIGN KEY FK_6C035B66FDA7B0BF');
        $this->addSql('DROP INDEX UNIQ_6C035B66FDA7B0BF ON mass');
        $this->addSql('ALTER TABLE mass DROP community_id');
    }
}
