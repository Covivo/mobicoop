<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201001143700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Action when all the public transport solutions of a Mass has been gathered
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (77, 'signal_debt', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (78, 'pay_after_carpool', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (79, 'pay_after_carpool_regular', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (80, 'confirm_direct_payment', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (81, 'confirm_direct_payment_regular', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (82, 'identity_proof_accepted', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (83, 'identity_proof_rejected', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (84, 'identity_proof_outdated', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");

        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (98, '77', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '1')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (99, '77', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '2')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (100, '77', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '3')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (101, '78', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '1')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (102, '78', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '2')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (103, '78', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '3')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (104, '79', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '1')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (105, '79', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '2')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (106, '79', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '3')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (107, '80', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '1')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (108, '80', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '2')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (109, '80', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '3')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (110, '81', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '1')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (111, '81', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '2')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (112, '81', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '3')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (113, '82', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '1')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (114, '82', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '2')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (115, '82', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '3')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (116, '83', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '1')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (117, '83', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '2')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (118, '83', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '3')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (119, '84', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '1')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (120, '84', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '2')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (121, '84', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '3')");

        $this->addSql('INSERT INTO user_notification (user_id, notification_id, active, created_date) 
        SELECT u.id, n.id, n.user_active_default, NOW()
        FROM user u JOIN notification n
        WHERE n.id IN (101,102,103,104,105,106,107,108,109,110,111,112)
        ');
        $this->addSql('UPDATE `user_notification` SET `active` =0 WHERE notification_id IN (102,103,105,106,108,109,111,112)');
        $this->addSql('UPDATE `notification` SET `position` =1 WHERE `medium_id` = 2');
        $this->addSql('UPDATE `notification` SET `position` =2 WHERE `medium_id` = 3');
        $this->addSql('UPDATE `notification` SET `position` =3 WHERE `medium_id` = 4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
