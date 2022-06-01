<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration
 */
final class Version20200514095100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // add solidary_transporter_schedule item
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (215, NULL, '1', 'action_create', 'Create an action')
        ");
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (216, NULL, '1', 'action_read', 'View an action')
        ");
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (217, NULL, '1', 'action_update', 'Update an action')
        ");
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (218, NULL, '1', 'action_delete', 'Delete an action')
        ");
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (219, NULL, '1', 'action_list', 'View the list of actions')
        ");
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (220, NULL, '1', 'action_manage', 'Manage the actions')
        ");
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (221, NULL, '1', 'solidary_animation_create', 'Create a solidaryAnimation')
        ");
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (222, NULL, '1', 'solidary_animation_list', 'List the solidaryAnimation of a Solidary')
        ");

        // add solidary_transporter_schedule item child to solidary_manage
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('220', '215')");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('220', '216')");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('220', '217')");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('220', '218')");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('220', '219')");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '220')");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('133', '221')");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('133', '222')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
