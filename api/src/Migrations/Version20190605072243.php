<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190605072243 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE uright CHANGE description description VARCHAR(255) DEFAULT NULL');

        // insert and update roles
        $this->addSql('UPDATE role set title=\'Super admin\', name=\'ROLE_SUPER_ADMIN\' WHERE id=1');
        $this->addSql('INSERT INTO `role` (`id`, `title`, `name`, `parent_id`) VALUES
        (3, \'Utilisateur avec inscription complète\', \'ROLE_USER_REGISTERED_FULL\', 2),
        (4, \'Utilisateur avec inscription minimale\', \'ROLE_USER_REGISTERED\', 3),
        (5, \'Utilisateur non inscrit ou non connecté\', \'ROLE_USER\', 4),
        (6, \'Utilisateur covimatch\', \'ROLE_MATCH\', 2);');

        // insert rights
        $this->addSql('INSERT INTO `uright` (`id`, `type`, `name`, `parent_id`, `description`) VALUES
        (1, 2, \'user_manage\', NULL, \'Manage users\'),
        (2, 2, \'user_car_manage\', NULL, \'Manage users cars\'),
        (3, 2, \'user_address_manage\', NULL, \'Manage users addresses\'),
        (4, 2, \'user_manage_self\', NULL, \'Manage its own profile\'),
        (5, 2, \'user_car_manage_self\', NULL, \'Manage its own cars\'),
        (6, 2, \'user_address_manage_self\', NULL, \'Manage its own addresses\'),
        (7, 2, \'carpool_manage\', NULL, \'Manage carpool data\'),
        (8, 2, \'carpool_manage_self\', NULL, \'Manage its own carpool data\'),
        (9, 2, \'article_manage\', NULL, \'Manage articles\'),
        (10, 2, \'community_manage\', NULL, \'Manage communities\'),
        (11, 2, \'community_manage_self\', NULL, \'Manage its own communities\'),
        (12, 2, \'event_manage\', NULL, \'Manage events\'),
        (13, 2, \'event_manage_self\', NULL, \'Manage its own events\'),
        (14, 2, \'relay_point_manage\', NULL, \'Manage relay points\'),
        (15, 2, \'territory_manage\', NULL, \'Manage territories\'),
        (16, 2, \'mass_manage\', NULL, \'Manage mass matching data\'),
        (17, 2, \'permission_manage\', NULL, \'Manage permissions\'),
        (18, 1, \'user_create\', 1, \'Create a user\'),
        (19, 1, \'user_update\', 1, \'Update a user\'),
        (20, 1, \'user_delete\', 1, \'Delete a user\'),
        (21, 1, \'user_password\', 1, \'Change user password\'),
        (22, 1, \'user_car_create\', 2, \'Create a car for a user\'),
        (23, 1, \'user_car_update\', 2, \'Update a car for a user\'),
        (24, 1, \'user_car_delete\', 2, \'Delete a car for a user\'),
        (25, 1, \'user_address_create\', 3, \'Create an address for a user\'),
        (26, 1, \'user_address_update\', 3, \'Update an address for a user\'),
        (27, 1, \'user_address_delete\', 3, \'Delete an address for a user\'),
        (28, 1, \'user_register\', NULL, \'Minimal registration\'),
        (29, 1, \'user_register_full\', NULL, \'Full registration\'),
        (30, 1, \'user_update_self\', 4, \'Update its own profile\'),
        (31, 1, \'user_delete_self\', 4, \'Delete its own profile\'),
        (32, 1, \'user_password_self\', 4, \'Change its own password\'),
        (33, 1, \'user_car_create_self\', 5, \'Create a car for itself\'),
        (34, 1, \'user_car_update_self\', 5, \'Update its own car\'),
        (35, 1, \'user_car_delete_self\', 5, \'Delete its own car\'),
        (36, 1, \'user_address_create_self\', 6, \'Create an address for itself\'),
        (37, 1, \'user_address_update_self\', 6, \'Update its own address\'),
        (38, 1, \'user_address_delete_self\', 6, \'Delete its own address\'),
        (39, 1, \'proposal_create\', 7, \'Create a proposal for a user\'),
        (40, 1, \'proposal_update\', 7, \'Update a proposal for a user\'),
        (41, 1, \'proposal_delete\', 7, \'Delete a proposal for a user\'),
        (42, 1, \'proposal_search\', NULL, \'Search a proposal\'),
        (43, 1, \'proposal_create_self\', NULL, \'Create its own proposal\'),
        (44, 1, \'proposal_validate\', 8, \'Validates its own proposal (after creation while disconnected)\'),
        (45, 1, \'proposal_update_self\', 8, \'Update its own proposal\'),
        (46, 1, \'proposal_delete_self\', 8, \'Delete its own proposal\'),
        (47, 1, \'article_create\', 9, \'Create an article\'),
        (48, 1, \'article_update\', 9, \'Update an article\'),
        (49, 1, \'article_delete\', 9, \'Delete an article\'),
        (50, 1, \'article_read\', NULL, \'Read an article\'),
        (51, 1, \'community_create\', NULL, \'Create a community\'),
        (52, 1, \'community_private_create\', 10, \'Create a private community\'),
        (53, 1, \'community_update\', 10, \'Update a community\'),
        (54, 1, \'community_delete\', 10, \'Delete a community\'),
        (55, 1, \'community_member_accept\', 10, \'Accept a member in a community\'),
        (56, 1, \'community_member_refuse\', 10, \'Refuse a member in a community\'),
        (57, 1, \'community_update_self\', 11, \'Update its own community\'),
        (58, 1, \'community_delete_self\', 11, \'Delete its own community\'),
        (59, 1, \'community_join\', NULL, \'Join a community\'),
        (60, 1, \'community_join_private\', NULL, \'Join a private community\'),
        (61, 1, \'community_leave\', NULL, \'Leave a community\'),
        (62, 1, \'community_read\', NULL, \'View a community\'),
        (63, 1, \'event_create\', NULL, \'Create an event\'),
        (64, 1, \'event_update\', 12, \'Update an event\'),
        (65, 1, \'event_delete\', 12, \'Delete an event\'),
        (66, 1, \'event_update_self\', 13, \'Update its own event\'),
        (67, 1, \'event_delete_self\', 13, \'Delete its own event\'),
        (68, 1, \'relay_point_create\', 14, \'Create a relay point\'),
        (69, 1, \'relay_point_update\', 14, \'Update a relay point\'),
        (70, 1, \'relay_point_delete\', 14, \'Delete a relay point\'),
        (71, 1, \'relay_point_type_create\', 14, \'Create a relay point type\'),
        (72, 1, \'relay_point_type_update\', 14, \'Update a relay point type\'),
        (73, 1, \'relay_point_type_delete\', 14, \'Delete a relay point type\'),
        (74, 1, \'relay_point_read\', NULL, \'View a relay point\'),
        (75, 1, \'territory_create\', 15, \'Create a territory\'),
        (76, 1, \'territory_update\', 15, \'Update a territory\'),
        (77, 1, \'territory_delete\', 15, \'Delete a territory\'),
        (78, 1, \'mass_create\', 16, \'Create mass matching data\'),
        (79, 1, \'mass_read\', 16, \'Read a mass matching data\'),
        (80, 1, \'mass_delete\', 16, \'Delete mass matching data\'),
        (81, 1, \'role_assign\', 17, \'Assign a role to a user\'),
        (82, 1, \'right_role_assign\', 17, \'Assign a right to a role\'),
        (83, 1, \'right_user_assign\', NULL, \'Assign a right to a user\'),
        (84, 1, \'proposal_results\', 8, \'View its own proposal results\'),
        (85, 1, \'community_list\', NULL, \'List communities\');');

        // insert role rights
        $this->addSql('INSERT INTO `role_right` (`role_id`, `right_id`) VALUES
        (1, 17),
        (2, 1),
        (2, 2),
        (2, 3),
        (2, 7),
        (2, 9),
        (2, 10),
        (2, 12),
        (2, 14),
        (2, 15),
        (2, 83),
        (3, 5),
        (3, 6),
        (3, 11),
        (3, 13),
        (3, 51),
        (3, 59),
        (3, 60),
        (3, 61),
        (3, 63),
        (4, 4),
        (4, 8),
        (5, 28),
        (5, 29),
        (5, 42),
        (5, 43),
        (5, 50),
        (5, 62),
        (5, 74),
        (5, 85),
        (6, 16);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE uright CHANGE description description VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
