<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate
 */
final class Version20210208163000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Add the interoperability rights for User resource

        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (257, NULL, '2', 'ROLE_INTEROPERABILITY', 'Interoperability platform');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (258, NULL, '1', 'interop_user_create', 'Create a User via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (259, NULL, '1', 'interop_user_read', 'Read a User via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (260, NULL, '1', 'interop_user_update', 'Update a User via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (261, NULL, '1', 'interop_user_delete', 'Delete a User via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (262, NULL, '1', 'interop_user_list', 'List Users via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (263, NULL, '1', 'interop_ad_create', 'Create an Ad via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (264, NULL, '1', 'interop_ad_read', 'Read an Ad via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (265, NULL, '1', 'interop_ad_update', 'Update an Ad via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (266, NULL, '1', 'interop_ad_delete', 'Delete an Ad via interoperability');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (267, NULL, '1', 'interop_ad_list', 'List Ads via interoperability');");

        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '258');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '259');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '260');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '261');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '262');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '263');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '264');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '265');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '266');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('257', '267');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('258', '14');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('263', '39');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
