<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial Gamification Model Migration
 */
final class Version20210623110600 extends AbstractMigration
{
    public function up(Schema $schema) : void
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
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (93, 'user_updated_avatar', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (94, 'user_updated_home_address', '1', NULL, NULL, '2021-06-23 10:59:26', NULL, '0', NULL);");

        // GamificationActionRule
        // $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (1, 'UserUpdateAvatar');");
        // $this->addSql("INSERT INTO `gamification_action_rule` (`id`, `name`) VALUES (2, 'UserUpdateHomeAddress');");

        // GamificationAction
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (1, '91', NULL, 'user_user_mail_validation');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (2, '92', NULL, 'user_phone_validation');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (3, '93', 1, 'user_updated_avatar');");
        $this->addSql("INSERT INTO `gamification_action` (`id`, `action_id`, `gamification_action_rule_id`, `name`) VALUES (4, '94', 2, 'user_updated_home_address');");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
