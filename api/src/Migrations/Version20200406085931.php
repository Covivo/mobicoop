<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200406085931 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF4318E9BFA2');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF4318E9BFA2 FOREIGN KEY (solidary_matching_id) REFERENCES solidary_matching (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF4318E9BFA2');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF4318E9BFA2 FOREIGN KEY (solidary_matching_id) REFERENCES solidary_solution (id)');
    }
}
