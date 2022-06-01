<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190613151216 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE community_security (id INT AUTO_INCREMENT NOT NULL, community_id INT NOT NULL, filename VARCHAR(255) NOT NULL, field1name VARCHAR(255) NOT NULL, field1type SMALLINT NOT NULL, field2name VARCHAR(255) NOT NULL, field2type SMALLINT NOT NULL, INDEX IDX_37DD10E6FDA7B0BF (community_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE community_security ADD CONSTRAINT FK_37DD10E6FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE community ADD proposals_hidden TINYINT(1) DEFAULT NULL, CHANGE private members_hidden TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE community_security');
        $this->addSql('ALTER TABLE community ADD private TINYINT(1) DEFAULT NULL, DROP members_hidden, DROP proposals_hidden');
    }
}
