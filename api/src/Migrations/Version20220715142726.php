<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220715142726 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (112, 'welcome', NULL, NULL, NULL, '2022-07-15 15:13:24', NULL, 0, NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (113, 'carpool_ask_posted_relaunch_1', NULL, NULL, NULL, '2022-07-15 15:13:24', NULL, 0, NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (114, 'carpool_ask_posted_relaunch_2', NULL, NULL, NULL, '2022-07-15 15:13:24', NULL, 0, NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (115, 'communication_internal_message_received_relaunch_1', NULL, NULL, NULL, '2022-07-15 15:13:24', NULL, 0, NULL);");
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (116, 'communication_internal_message_received_relaunch_2', NULL, NULL, NULL, '2022-07-15 15:13:24', NULL, 0, NULL);");

        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (138, 112, 2, NULL, 1, NULL, '2022-07-15 15:15:45', NULL, 1, 0, 0);");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (139, 113, 2, NULL, 1, NULL, '2022-07-15 15:15:45', NULL, 1, 0, 0);");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (140, 114, 2, NULL, 1, NULL, '2022-07-15 15:15:45', NULL, 1, 0, 0);");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (141, 115, 2, NULL, 1, NULL, '2022-07-15 15:15:45', NULL, 1, 0, 0);");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (142, 116, 2, NULL, 1, NULL, '2022-07-15 15:15:45', NULL, 1, 0, 0);");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
