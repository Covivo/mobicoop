<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate.
 */
final class Version20210524152700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // solidary updates in auth items
        $this->addSql('
        INSERT INTO `auth_rule` (`id`, `name`) VALUES
        (30, \'StructureOperator\'),
        (31, \'SolidaryOperator\');
        ');

        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (274, NULL, '2', 'ROLE_SOLIDARY_ADMIN', 'Solidary administrator');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (275, 30, '1', 'structure_read_operator', 'View a structure in which one operates');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (276, 30, '1', 'structure_update_operator', 'Edit a structure in which ine operates');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (277, NULL, '1', 'solidary_list_operator', 'List solidary records belonging to structures in which one operates');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (278, 31, '1', 'solidary_read_operator', 'View a solidary record belonging to a structure in which one operates');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (279, 31, '1', 'solidary_update_operator', 'Update a solidary record belonging to a structure in which one operates');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (280, 31, '1', 'solidary_delete_operator', 'Delete a solidary record belonging to a structure in which one operates');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (281, NULL, '1', 'user_list_community_members', 'List users belonging to communities one manages');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (282, NULL, '1', 'user_list_communication', 'List users accepting communication messages');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (283, NULL, '1', 'structure_list_operator', 'List structures in which one operates');");

        $this->addSql('UPDATE `auth_item` SET name = "ROLE_SOLIDARY_OPERATOR" WHERE id=10');
        $this->addSql('UPDATE `auth_item_child` SET `parent_id` = 2, `child_id`=274 WHERE `parent_id` = 2 and `child_id`=10');
        $this->addSql('UPDATE `auth_item_child` SET `parent_id` = 2, `child_id`=133 WHERE `parent_id` = 10 and `child_id`=133');
        $this->addSql('UPDATE `auth_item_child` SET `parent_id` = 2, `child_id`=195 WHERE `parent_id` = 10 and `child_id`=195');

        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('274', '10');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('275', '191');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('276', '192');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('277', '132');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('278', '129');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('279', '130');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('280', '131');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('281', '20');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('282', '20');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '20');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '275');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '277');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '278');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '279');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '280');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '283');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('283', '194');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('274', '276');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
