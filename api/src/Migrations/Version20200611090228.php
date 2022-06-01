<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200611090228 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mass_ptjourney (id INT AUTO_INCREMENT NOT NULL, mass_person_id INT NOT NULL, distance INT DEFAULT NULL, duration INT DEFAULT NULL, distance_walk_from_home INT DEFAULT NULL, duration_walk_from_home INT DEFAULT NULL, distance_walk_from_work INT DEFAULT NULL, duration_walk_from_work INT DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_C8317A3E828090B4 (mass_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mass_ptjourney ADD CONSTRAINT FK_C8317A3E828090B4 FOREIGN KEY (mass_person_id) REFERENCES mass_person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass ADD getting_public_transportation_potential_date DATETIME DEFAULT NULL, ADD got_public_transportation_potential_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE mass_ptjourney');
        $this->addSql('ALTER TABLE mass DROP getting_public_transportation_potential_date, DROP got_public_transportation_potential_date');
    }
}
