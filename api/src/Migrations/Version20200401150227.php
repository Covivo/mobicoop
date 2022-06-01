<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200401150227 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_user CHANGE m_min_time m_min_time TIME DEFAULT NULL, CHANGE m_max_time m_max_time TIME DEFAULT NULL, CHANGE a_min_time a_min_time TIME DEFAULT NULL, CHANGE a_max_time a_max_time TIME DEFAULT NULL, CHANGE e_min_time e_min_time TIME DEFAULT NULL, CHANGE e_max_time e_max_time TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_user CHANGE m_min_time m_min_time DATETIME DEFAULT NULL, CHANGE m_max_time m_max_time DATETIME DEFAULT NULL, CHANGE a_min_time a_min_time DATETIME DEFAULT NULL, CHANGE a_max_time a_max_time DATETIME DEFAULT NULL, CHANGE e_min_time e_min_time DATETIME DEFAULT NULL, CHANGE e_max_time e_max_time DATETIME DEFAULT NULL');
    }
}
