<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191116160311 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE user ADD phone_display SMALLINT NOT NULL, CHANGE given_name given_name VARCHAR(100) DEFAULT NULL, CHANGE family_name family_name VARCHAR(100) DEFAULT NULL, CHANGE password password VARCHAR(100) DEFAULT NULL, CHANGE nationality nationality VARCHAR(100) DEFAULT NULL, CHANGE birth_date birth_date DATE DEFAULT NULL, CHANGE telephone telephone VARCHAR(100) DEFAULT NULL, CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT NULL, CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT NULL, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL, CHANGE pwd_token pwd_token VARCHAR(100) DEFAULT NULL, CHANGE geo_token geo_token VARCHAR(100) DEFAULT NULL, CHANGE language language VARCHAR(10) DEFAULT NULL, CHANGE pwd_token_date pwd_token_date DATETIME DEFAULT NULL, CHANGE updated_date updated_date DATETIME DEFAULT NULL, CHANGE validated_date validated_date DATETIME DEFAULT NULL, CHANGE validated_date_token validated_date_token VARCHAR(100) DEFAULT NULL, CHANGE facebook_id facebook_id VARCHAR(100) DEFAULT NULL, CHANGE smoke smoke INT DEFAULT NULL, CHANGE music music TINYINT(1) DEFAULT NULL, CHANGE music_favorites music_favorites VARCHAR(255) DEFAULT NULL, CHANGE chat chat TINYINT(1) DEFAULT NULL, CHANGE chat_favorites chat_favorites VARCHAR(255) DEFAULT NULL, CHANGE news_subscription news_subscription TINYINT(1) DEFAULT NULL, CHANGE phone_token phone_token VARCHAR(100) DEFAULT NULL, CHANGE ios_app_id ios_app_id VARCHAR(100) DEFAULT NULL, CHANGE android_app_id android_app_id VARCHAR(100) DEFAULT NULL, CHANGE phone_validated_date phone_validated_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE user DROP phone_display, CHANGE given_name given_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE family_name family_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password password VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE nationality nationality VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE birth_date birth_date DATE DEFAULT \'NULL\', CHANGE telephone telephone VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL, CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT \'NULL\', CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT \'NULL\', CHANGE smoke smoke INT DEFAULT NULL, CHANGE music music TINYINT(1) DEFAULT \'NULL\', CHANGE music_favorites music_favorites VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE chat chat TINYINT(1) DEFAULT \'NULL\', CHANGE chat_favorites chat_favorites VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE news_subscription news_subscription TINYINT(1) DEFAULT \'NULL\', CHANGE validated_date validated_date DATETIME DEFAULT \'NULL\', CHANGE validated_date_token validated_date_token VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE updated_date updated_date DATETIME DEFAULT \'NULL\', CHANGE pwd_token_date pwd_token_date DATETIME DEFAULT \'NULL\', CHANGE pwd_token pwd_token VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE geo_token geo_token VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE phone_token phone_token VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE phone_validated_date phone_validated_date DATETIME DEFAULT \'NULL\', CHANGE ios_app_id ios_app_id VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE android_app_id android_app_id VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE language language VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE facebook_id facebook_id VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
