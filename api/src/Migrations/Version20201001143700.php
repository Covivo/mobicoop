<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201001143700 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Action when all the public transport solutions of a Mass has been gathered
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (77, 'signal_debt', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (78, 'pay_after_carpool', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (79, 'pay_after_carpool_regular', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (80, 'confirm_direct_payment', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (81, 'confirm_direct_payment_regular', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (82, 'identity_proof_accepted', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (83, 'identity_proof_rejected', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (84, 'identity_proof_outdated', NULL, NULL, NULL, '2020-10-01 14:37:00', NULL, '0', NULL)");

        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '77', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '77', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '77', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '78', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '78', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '78', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '79', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '79', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '79', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '80', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '80', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '80', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '81', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '81', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '81', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '1', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '82', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '82', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '82', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '83', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '83', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '83', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '84', '2', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '84', '3', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '84', '4', NULL, '1', NULL, '2020-10-01 14:37:00', NULL, '1', '0', '0')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
