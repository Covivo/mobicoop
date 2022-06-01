<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200225083121 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE relay_point ADD structure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9C2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('CREATE INDEX IDX_A9BE6C9C2534008B ON relay_point (structure_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9C2534008B');
        $this->addSql('DROP INDEX IDX_A9BE6C9C2534008B ON relay_point');
        $this->addSql('ALTER TABLE relay_point DROP structure_id');
    }
}
