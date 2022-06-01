<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200609081752 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('UPDATE structure SET m_min_time=\'08:00\' WHERE structure.m_min_time IS NULL');
        $this->addSql('UPDATE structure SET m_max_time=\'12:00\' WHERE structure.m_max_time IS NULL');
        $this->addSql('UPDATE structure SET a_min_time=\'14:00\' WHERE structure.a_min_time IS NULL');
        $this->addSql('UPDATE structure SET a_max_time=\'19:00\' WHERE structure.a_max_time IS NULL');
        $this->addSql('UPDATE structure SET e_min_time=\'20:00\' WHERE structure.e_min_time IS NULL');
        $this->addSql('UPDATE structure SET e_max_time=\'22:00\' WHERE structure.e_max_time IS NULL');
        $this->addSql('ALTER TABLE structure CHANGE m_min_time m_min_time TIME NOT NULL, CHANGE m_max_time m_max_time TIME NOT NULL, CHANGE a_min_time a_min_time TIME NOT NULL, CHANGE a_max_time a_max_time TIME NOT NULL, CHANGE e_min_time e_min_time TIME NOT NULL, CHANGE e_max_time e_max_time TIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE structure CHANGE m_min_time m_min_time TIME DEFAULT NULL, CHANGE m_max_time m_max_time TIME DEFAULT NULL, CHANGE a_min_time a_min_time TIME DEFAULT NULL, CHANGE a_max_time a_max_time TIME DEFAULT NULL, CHANGE e_min_time e_min_time TIME DEFAULT NULL, CHANGE e_max_time e_max_time TIME DEFAULT NULL');
    }
}
