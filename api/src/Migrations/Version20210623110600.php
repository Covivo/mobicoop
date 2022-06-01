<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial Gamification Model Migration
 */
final class Version20210623110600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Update old actions name
        $this->addSql("UPDATE `action` SET `name` = 'user_phone_validation_asked' WHERE `action`.`id` = 13;");
        $this->addSql("UPDATE `action` SET `name` = 'user_deleted_with_pending_drivers' WHERE `action`.`id` = 19;");
        $this->addSql("UPDATE `action` SET `name` = 'user_deleted_with_pending_passengers' WHERE `action`.`id` = 20;");
        $this->addSql("UPDATE `action` SET `name` = 'carpool_ad_posted' WHERE `action`.`id` = 11;");
        $this->addSql("UPDATE `action` SET `name` = 'carpool_ad_deleted' WHERE `action`.`id` = 12;");
        $this->addSql("UPDATE `action` SET `name` = 'carpool_ad_deleted_with_pending_passengers' WHERE `action`.`id` = 15;");
        $this->addSql("UPDATE `action` SET `name` = 'carpool_ad_deleted_with_pending_passengers_urgent' WHERE `action`.`id` = 16;");
        $this->addSql("UPDATE `action` SET `name` = 'carpool_ad_deleted_with_pending_drivers' WHERE `action`.`id` = 17;");
        $this->addSql("UPDATE `action` SET `name` = 'carpool_ad_deleted_with_pending_drivers_urgent' WHERE `action`.`id` = 18;");
        $this->addSql("UPDATE `action` SET `name` = 'event_created' WHERE `action`.`id` = 21;");


        // New actions
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (91, 'user_mail_validation', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (92, 'user_phone_validation', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (93, 'user_avatar_uploaded', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (94, 'user_home_address_updated', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (95, 'community_joined', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (96, 'community_created', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (97, 'carpool_ad_renewed', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (98, 'electronic_payment_made', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (99, 'user_phone_updated', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");

        // Update the existings actions to be sure they are logged
        $this->addSql("UPDATE `action` SET `in_log` = '1' WHERE `action`.`id` = 5;");
        $this->addSql("UPDATE `action` SET `in_log` = '1' WHERE `action`.`id` = 7;");
        $this->addSql("UPDATE `action` SET `in_log` = '1' WHERE `action`.`id` = 11;");
        $this->addSql("UPDATE `action` SET `in_log` = '1' WHERE `action`.`id` = 21;");
        $this->addSql("UPDATE `action` SET `in_log` = '1' WHERE `action`.`id` = 82;");

        // GamificationActionRule
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (1, 'HasOnlyOneAd');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (2, 'HasAtLeastNAd');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (3, 'AdInCommunity');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (4, 'IsCarpoolAccepter');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (5, 'CarpoolInCommunity');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (6, 'IsSolidaryExclusive');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (7, 'HasAtLeastNCarpooledKm');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (8, 'HasAtLeastNCarpooledCo2Saved');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (9, 'FirstMessageAnswer');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (10, 'HasARelayPointAsWaypoint');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (11, 'AdInEvent');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (12, 'HasOnlyOneElectronicPayment');");
        $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (13, 'CarpoolInEvent');");

        // GamificationAction
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (1, '91', NULL, 'user_mail_validation');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (2, '92', NULL, 'user_phone_validation');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (3, '93', NULL, 'user_avatar_uploaded');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (4, '94', NULL, 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (5, '11', '1', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (6, '11', '2', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (7, '95', NULL, 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (8, '11', '3', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (9, '5', '4', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (10, '5', '5', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (11, '11', '6', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (12, '5', '7', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (13, '5', '8', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (14, '7', '9', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (15, '97', NULL, 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (16, '11', '10', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (17, '11', '11', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (18, '82', NULL, 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (19, '98', '12', 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (20, '99', NULL, 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (21, '21', NULL, 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (22, '96', NULL, 'user_home_address_updated');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (23, '5', '13', 'user_home_address_updated');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
