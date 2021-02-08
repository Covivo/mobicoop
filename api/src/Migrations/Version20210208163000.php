<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate
 */
final class Version20210208163000 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // Add the interoperability right for User resource

        $this->addSql("INSERT INTO `auth_rule` (`id`, `name`) VALUES ('30', 'InteropUserSelf');");
        
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (257, NULL, '2', 'ROLE_INTEROPERABILITY', 'Interoperability platform');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (258, NULL, '1', 'interop_user_create', 'Create a User via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (259, NULL, '1', 'interop_user_read', 'Read a User via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (260, NULL, '1', 'interop_user_update', 'Update a User via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (261, NULL, '1', 'interop_user_delete', 'Delete a User via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (262, NULL, '1', 'interop_user_list', 'List Users via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (263, '30', '1', 'interop_user_read_self', 'Read its own profile via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (264, '30', '1', 'interop_user_update_self', 'Update its own profile via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (265, '30', '1', 'interop_user_delete_self', 'Delete its own profile via interoperability');");

        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '22');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('22', '258');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('22', '259');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('22', '260');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('22', '261');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('22', '262');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('263', '259');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('264', '260');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('265', '261');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('4', '263');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('4', '264');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('4', '265');");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
