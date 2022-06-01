<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201210110336 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE journey (id INT AUTO_INCREMENT NOT NULL, proposal_id INT NOT NULL, user_id INT NOT NULL, user_name VARCHAR(255) DEFAULT NULL, origin VARCHAR(255) NOT NULL, latitude_origin NUMERIC(10, 6) DEFAULT NULL, longitude_origin NUMERIC(10, 6) DEFAULT NULL, destination VARCHAR(255) NOT NULL, latitude_destination NUMERIC(10, 6) DEFAULT NULL, longitude_destination NUMERIC(10, 6) DEFAULT NULL, frequency SMALLINT NOT NULL, from_date DATE NOT NULL, to_date DATE DEFAULT NULL, time TIME DEFAULT NULL, days VARCHAR(255) DEFAULT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_ORIGIN_DESTINATION (origin, destination), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE journey');
    }
}
