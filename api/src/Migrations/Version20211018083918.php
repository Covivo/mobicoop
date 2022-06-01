<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018083918 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6990BEA15');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411990BEA15');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6990BEA15');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411990BEA15');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
    }
}
