<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190520100919 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE waypoint CHANGE is_destination destination TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE territory ADD detail MULTIPOLYGON NOT NULL COMMENT \'(DC2Type:multipolygon)\'');
        $this->addSql('ALTER TABLE user CHANGE created_date created_date DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE territory DROP detail');
        $this->addSql('ALTER TABLE user CHANGE created_date created_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE waypoint CHANGE destination is_destination TINYINT(1) NOT NULL');
    }
}
