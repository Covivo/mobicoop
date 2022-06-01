<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200406151944 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA42534008B');
        $this->addSql('DROP INDEX IDX_1896BCA42534008B ON solidary');
        $this->addSql('ALTER TABLE solidary DROP structure_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary ADD structure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA42534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('CREATE INDEX IDX_1896BCA42534008B ON solidary (structure_id)');
    }
}
