<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190718153203 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD pupdtime INT DEFAULT NULL, ADD token VARCHAR(100) DEFAULT NULL, CHANGE given_name given_name VARCHAR(100) DEFAULT NULL, CHANGE family_name family_name VARCHAR(100) DEFAULT NULL, CHANGE password password VARCHAR(100) DEFAULT NULL, CHANGE nationality nationality VARCHAR(100) DEFAULT NULL, CHANGE birth_date birth_date DATE DEFAULT NULL, CHANGE telephone telephone VARCHAR(100) DEFAULT NULL, CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT NULL, CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT NULL, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP pupdtime, DROP token, CHANGE given_name given_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE family_name family_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password password VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE nationality nationality VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE birth_date birth_date DATE DEFAULT \'NULL\', CHANGE telephone telephone VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL, CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT \'NULL\', CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT \'NULL\'');
    }
}
