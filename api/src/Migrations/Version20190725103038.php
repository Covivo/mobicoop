<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190725103038 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE user CHANGE given_name given_name VARCHAR(100) DEFAULT NULL, CHANGE family_name family_name VARCHAR(100) DEFAULT NULL, CHANGE password password VARCHAR(100) DEFAULT NULL, CHANGE nationality nationality VARCHAR(100) DEFAULT NULL, CHANGE birth_date birth_date DATE DEFAULT NULL, CHANGE telephone telephone VARCHAR(100) DEFAULT NULL, CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT NULL, CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT NULL, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL, CHANGE pupdtime pupdtime DATETIME DEFAULT NULL, CHANGE token token VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE given_name given_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE family_name family_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password password VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE nationality nationality VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE birth_date birth_date DATE DEFAULT \'NULL\', CHANGE telephone telephone VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL, CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT \'NULL\', CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT \'NULL\', CHANGE pupdtime pupdtime INT DEFAULT NULL, CHANGE token token VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
