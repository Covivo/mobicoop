<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200307180232 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_role DROP FOREIGN KEY FK_5247AFCAD60322AC');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6A727ACA70');
        $this->addSql('ALTER TABLE role_right DROP FOREIGN KEY FK_43169D3BD60322AC');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE role_right DROP FOREIGN KEY FK_43169D3B54976835');
        $this->addSql('ALTER TABLE user_right DROP FOREIGN KEY FK_56088E4C54976835');
        $this->addSql('CREATE TABLE app_auth_item (app_id INT NOT NULL, auth_item_id INT NOT NULL, INDEX IDX_99529A9F7987212D (app_id), INDEX IDX_99529A9F5C4B72AD (auth_item_id), PRIMARY KEY(app_id, auth_item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auth_item (id INT AUTO_INCREMENT NOT NULL, auth_rule_id INT DEFAULT NULL, type INT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_313DC5AA3A6A23A2 (auth_rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auth_item_child (parent_id INT NOT NULL, child_id INT NOT NULL, INDEX IDX_1611424D727ACA70 (parent_id), INDEX IDX_1611424DDD62C21B (child_id), PRIMARY KEY(parent_id, child_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auth_rule (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_auth_assignment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, auth_item_id INT NOT NULL, territory_id INT DEFAULT NULL, INDEX IDX_3C1C2581A76ED395 (user_id), INDEX IDX_3C1C25815C4B72AD (auth_item_id), INDEX IDX_3C1C258173F74AD4 (territory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_auth_item ADD CONSTRAINT FK_99529A9F7987212D FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_auth_item ADD CONSTRAINT FK_99529A9F5C4B72AD FOREIGN KEY (auth_item_id) REFERENCES auth_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE auth_item ADD CONSTRAINT FK_313DC5AA3A6A23A2 FOREIGN KEY (auth_rule_id) REFERENCES auth_rule (id)');
        $this->addSql('ALTER TABLE auth_item_child ADD CONSTRAINT FK_1611424D727ACA70 FOREIGN KEY (parent_id) REFERENCES auth_item (id)');
        $this->addSql('ALTER TABLE auth_item_child ADD CONSTRAINT FK_1611424DDD62C21B FOREIGN KEY (child_id) REFERENCES auth_item (id)');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C2581A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C25815C4B72AD FOREIGN KEY (auth_item_id) REFERENCES auth_item (id)');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C258173F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');

        $this->addSql('
        INSERT INTO `auth_rule` (`id`, `name`) VALUES
        (1, \'UserSelf\'),
        (2, \'MessageActor\'),
        (3, \'MessageAuthor\'),
        (4, \'AdAuthor\'),
        (5, \'MatchingActor\'),
        (6, \'CommunityJoined\'),
        (7, \'CommunityManager\'),
        (8, \'EventAuthor\'),
        (9, \'RelayPointAuthor\'),
        (10, \'MassAuthor\'),
        (11, \'SolidaryVolunteerSelf\'),
        (12, \'SolidaryBeneficiarySelf\'),
        (13, \'CampaignAuthor\'),
        (14, \'MessageSender\'),
        (15, \'AskActor\'),
        (16, \'AskAuthor\'),
        (17, \'MobileUser\'),
        (18, \'DynamicAuthor\'),
        (19, \'DynamicAskActor\'),
        (20, \'DynamicAskAuthor\');
        ');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (1, NULL, 2, 'ROLE_SUPER_ADMIN', 'Super admin'),
        (2, NULL, 2, 'ROLE_ADMIN', 'Admin'),
        (3, NULL, 2, 'ROLE_USER_REGISTERED_FULL', 'User with full registration'),
        (4, NULL, 2, 'ROLE_USER_REGISTERED_MINIMAL', 'User with minimal registration'),
        (5, NULL, 2, 'ROLE_USER', 'Unregistered or disconnected user'),
        (6, NULL, 2, 'ROLE_MASS_MATCH', 'Mobimatch user'),
        (7, NULL, 2, 'ROLE_COMMUNITY_MANAGER', 'Community manager'),
        (8, NULL, 2, 'ROLE_COMMUNITY_MANAGER_PUBLIC', 'Community manager for public communities'),
        (9, NULL, 2, 'ROLE_COMMUNITY_MANAGER_PRIVATE', 'Community manager for private communities'),
        (10, NULL, 2, 'ROLE_SOLIDARY_MANAGER', 'Solidary manager'),
        (11, NULL, 2, 'ROLE_SOLIDARY_VOLUNTEER', 'Solidary volunteer'),
        (12, NULL, 2, 'ROLE_SOLIDARY_BENEFICIARY', 'Solidary beneficiary'),
        (13, NULL, 2, 'ROLE_COMMUNICATION_MANAGER', 'Communication manager'),
        (14, NULL, 1, 'user_register', 'Minimal registration'),
        (15, NULL, 1, 'user_register_full', 'Full registration'),
        (16, NULL, 1, 'user_create', 'Create a user'),
        (17, NULL, 1, 'user_read', 'View a user'),
        (18, NULL, 1, 'user_update', 'Update a user'),
        (19, NULL, 1, 'user_delete', 'Delete a user'),
        (20, NULL, 1, 'user_list', 'View the list of users'),
        (21, NULL, 1, 'user_password', 'Change user password'),
        (22, NULL, 1, 'user_manage', 'Manage users'),
        (23, 1, 1, 'user_read_self', 'Read its own profile'),
        (24, 1, 1, 'user_update_self', 'Update its own profile'),
        (25, 1, 1, 'user_delete_self', 'Delete its own profile'),
        (26, 1, 1, 'user_password_self', 'Change its own password'),
        (27, NULL, 1, 'user_message_create', 'Create a message'),
        (28, NULL, 1, 'user_message_read', 'Read a message of a user'),
        (29, NULL, 1, 'user_message_delete', 'Delete a message of a user'),
        (30, NULL, 1, 'user_message_manage', 'Manage user messages'),
        (31, 2, 1, 'user_message_read_self', 'Read its own messages'),
        (32, 3, 1, 'user_message_delete_self', 'Delete its own messages'),
        (33, NULL, 1, 'ad_create', 'Create an ad'),
        (34, NULL, 1, 'ad_read', 'View a user ad'),
        (35, NULL, 1, 'ad_update', 'Update an ad for a user'),
        (36, NULL, 1, 'ad_delete', 'Delete an ad for a user'),
        (37, NULL, 1, 'ad_list', 'View a list of ads'),
        (38, NULL, 1, 'ad_manage', 'Manages ads'),
        (39, NULL, 1, 'ad_search_create', 'Create an unpublished search ad and get the results'),
        (40, NULL, 1, 'ad_results', 'View the results of an ad'),
        (41, NULL, 1, 'ad_create_delegate', 'Create an ad for a user'),
        (42, 4, 1, 'ad_update_self', 'Update its own ad'),
        (43, 4, 1, 'ad_delete_self', 'Delete its own ad'),
        (44, 4, 1, 'ad_results_self', 'View the results of its own ad'),
        (45, NULL, 1, 'ad_list_self', 'View the list of its own ads'),
        (46, NULL, 1, 'ad_ask_create', 'Create an ask for an ad'),
        (47, NULL, 1, 'article_create', 'Create an article'),
        (48, NULL, 1, 'article_read', 'Read an article'),
        (49, NULL, 1, 'article_update', 'Update an article'),
        (50, NULL, 1, 'article_delete', 'Delete an article'),
        (51, NULL, 1, 'article_list', 'View the list of articles'),
        (52, NULL, 1, 'article_manage', 'Manage articles'),
        (53, NULL, 1, 'community_create', 'Create a community'),
        (54, NULL, 1, 'community_private_create', 'Create a private community'),
        (55, NULL, 1, 'community_read', 'View a community'),
        (56, 6, 1, 'community_private_read', 'View a private community'),
        (57, NULL, 1, 'community_join', 'Join/leave a community'),
        (58, NULL, 1, 'community_join_private', 'Join/leave a private community'),
        (59, NULL, 1, 'community_read_details', 'View the details of a community'),
        (60, NULL, 1, 'community_private_read_details', 'View the details of a private community'),
        (61, NULL, 1, 'community_update', 'Update a community'),
        (62, NULL, 1, 'community_delete', 'Delete a community'),
        (63, NULL, 1, 'community_list', 'View the list of communities'),
        (64, NULL, 1, 'community_member_list', 'View the list of members of a community'),
        (65, NULL, 1, 'community_membership', 'Manage a community membership'),
        (66, NULL, 1, 'community_dashboard', 'View the dashboard of a community'),
        (67, NULL, 1, 'community_contact', 'Contact the members of a community'),
        (68, NULL, 1, 'community_manage', 'Manage the communities'),
        (69, 7, 1, 'community_read_details_self', 'View the details of its managed community'),
        (70, 7, 1, 'community_private_read_details_self', 'View the details of its managed private community'),
        (71, 7, 1, 'community_update_self', 'Update its managed community'),
        (72, 7, 1, 'community_delete_self', 'Delete its managed community'),
        (73, 7, 1, 'community_member_list_self', 'View the list of members of its managed community'),
        (74, 7, 1, 'community_membership_self', 'Manage its managed community membership'),
        (75, 7, 1, 'community_dashboard_self', 'View the dashboard of its managed community'),
        (76, 7, 1, 'community_contact_self', 'Contact the members of its managed community'),
        (77, NULL, 1, 'event_create', 'Create an event'),
        (78, NULL, 1, 'event_read', 'View an event'),
        (79, NULL, 1, 'event_update', 'Update an event'),
        (80, NULL, 1, 'event_delete', 'Delete an event'),
        (81, NULL, 1, 'event_list', 'View the list of events'),
        (82, NULL, 1, 'event_manage', 'Manage the events'),
        (83, 8, 1, 'event_update_self', 'Update its own event'),
        (84, 8, 1, 'event_delete_self', 'Delete its own event'),
        (85, NULL, 1, 'relay_point_create', 'Create a relay point'),
        (86, NULL, 1, 'relay_point_read', 'View a relay point'),
        (87, NULL, 1, 'relay_point_update', 'Update a relay point'),
        (88, NULL, 1, 'relay_point_delete', 'Delete a relay point'),
        (89, NULL, 1, 'relay_point_list', 'View the list of relaypoints'),
        (90, NULL, 1, 'relay_point_manage', 'Manage the relaypoints'),
        (91, 9, 1, 'relay_point_update_self', 'Update its own relay point'),
        (92, 9, 1, 'relay_point_delete_self', 'Delete its own relay point'),
        (93, NULL, 1, 'relay_point_type_create', 'Create a relay point type'),
        (94, NULL, 1, 'relay_point_type_read', 'View a relay point type'),
        (95, NULL, 1, 'relay_point_type_update', 'Update a relay point type'),
        (96, NULL, 1, 'relay_point_type_delete', 'Delete a relay point type'),
        (97, NULL, 1, 'relay_point_type_list', 'View the list of relay point types'),
        (98, NULL, 1, 'relay_point_type_manage', 'Manage the relay point types'),
        (99, NULL, 1, 'territory_create', 'Create a territory'),
        (100, NULL, 1, 'territory_read', 'Read a territory'),
        (101, NULL, 1, 'territory_update', 'Update a territory'),
        (102, NULL, 1, 'territory_delete', 'Delete a territory'),
        (103, NULL, 1, 'territory_list', 'View the list of territories'),
        (104, NULL, 1, 'territory_manage', 'Manage the territories'),
        (105, NULL, 1, 'mass_create', 'Create mass matching data'),
        (106, NULL, 1, 'mass_read', 'Read a mass matching data'),
        (107, NULL, 1, 'mass_delete', 'Delete mass matching data'),
        (108, NULL, 1, 'mass_list', 'View the list of mass matching data'),
        (109, NULL, 1, 'mass_manage', 'Manage the mass mathcing data'),
        (110, 10, 1, 'mass_read_self', 'Read its own mass matching data'),
        (111, 10, 1, 'mass_delete_self', 'Delete its own mass matching data'),
        (112, NULL, 1, 'solidary_volunteer_register', 'Register as a solidary volunteer'),
        (113, NULL, 1, 'solidary_volunteer_create', 'Create a solidary volunteer'),
        (114, NULL, 1, 'solidary_volunteer_update', 'Update a solidary volunteer'),
        (115, NULL, 1, 'solidary_volunteer_delete', 'Delete a solidary volunteer'),
        (116, NULL, 1, 'solidary_volunteer_list', 'View the list of solidary volunteers'),
        (117, NULL, 1, 'solidary_volunteer_manage', 'Manage the solidary volunteers'),
        (118, 11, 1, 'solidary_volunteer_update_self', 'Update its own solidary volunteer profile'),
        (119, 11, 1, 'solidary_volunteer_delete_self', 'Delete its own solidary volunteer profile'),
        (120, NULL, 1, 'solidary_beneficiary_register', 'Register as a solidary beneficiary'),
        (121, NULL, 1, 'solidary_beneficiary_create', 'Create a solidary beneficiary'),
        (122, NULL, 1, 'solidary_beneficiary_update', 'Update a solidary beneficiary'),
        (123, NULL, 1, 'solidary_beneficiary_delete', 'Delete a solidary beneficiary'),
        (124, NULL, 1, 'solidary_beneficiary_list', 'View the list of solidary beneficiaries'),
        (125, NULL, 1, 'solidary_beneficiary_manage', 'Manage the solidary beneficiaries'),
        (126, 12, 1, 'solidary_beneficiary_update_self', 'Update its own solidary beneficiary profile'),
        (127, 12, 1, 'solidary_beneficiary_delete_self', 'Delete its own solidary beneficiary profile'),
        (128, NULL, 1, 'solidary_create', 'Create a solidary record'),
        (129, NULL, 1, 'solidary_read', 'Read a solidary record'),
        (130, NULL, 1, 'solidary_update', 'Update a solidary record'),
        (131, NULL, 1, 'solidary_delete', 'Delete a solidary record'),
        (132, NULL, 1, 'solidary_list', 'View the list of solidary records'),
        (133, NULL, 1, 'solidary_manage', 'Manage the solidary records'),
        (134, NULL, 1, 'campaign_create', 'Create a campaign'),
        (135, NULL, 1, 'campaign_read', 'View a campaign'),
        (136, NULL, 1, 'campaign_update', 'Update a campaign'),
        (137, NULL, 1, 'campaign_delete', 'Delete a campaign'),
        (138, NULL, 1, 'campaign_list', 'View the list of campaigns'),
        (139, NULL, 1, 'campaign_manage', 'Manage the campaigns'),
        (140, 13, 1, 'campaign_read_self', 'View its own campaign'),
        (141, 13, 1, 'campaign_update_self', 'Update its own campaign'),
        (142, 13, 1, 'campaign_delete_self', 'Delete its own campaign'),
        (143, NULL, 1, 'check_permission', 'Check a permission on an auth item'),
        (144, NULL, 1, 'auth_item_assign', 'Assign an auth item to a user'),
        (145, NULL, 1, 'access_admin', 'Get access to administration'),
        (146, NULL, 1, 'communication_contact', 'Send a contact message'),
        (147, 5, 1, 'ad_ask_create_self', 'Create its own ask for an ad'),
        (148, NULL, 1, 'ad_ask_read', 'Read an ask for an ad'),
        (149, 15, 1, 'ad_ask_read_self', 'Read its own ask for an ad'),
        (150, NULL, 1, 'ad_ask_update', 'Update an ask for an ad'),
        (151, 15, 1, 'ad_ask_update_self', 'Update its own ask for an ad'),
        (152, NULL, 1, 'ad_ask_delete', 'Delete an ask for an ad'),
        (153, 16, 1, 'ad_ask_delete_self', 'Delete its own ask for an ad'),
        (154, 17, 1, 'dynamic_ad_create', 'Create a dynamic ad'),
        (155, NULL, 1, 'dynamic_ad_read', 'Read a dynamic ad'),
        (156, 18, 1, 'dynamic_ad_read_self', 'Read its own dynamic ad'),
        (157, 18, 1, 'dynamic_ad_update', 'Update a dynamic ad'),
        (158, NULL, 1, 'dynamic_ad_delete', 'Delete a dynamic ad'),
        (159, 18, 1, 'dynamic_ad_delete_self', 'Delete its own dynamic ad'),
        (160, NULL, 1, 'dynamic_ad_list', 'View the list of dynamic ads'),
        (161, 5, 1, 'dynamic_ask_create', 'Create an ask for a dynamic ad'),
        (162, NULL, 1, 'dynamic_ask_read', 'Read an ask for a dynamic ad'),
        (163, 19, 1, 'dynamic_ask_read_self', 'Read its on ask for a dynamic ad'),
        (164, 19, 1, 'dynamic_ask_update', 'Update an ask for a dynamic ad'),
        (165, NULL, 1, 'dynamic_ask_delete', 'Delete an ask for a dynamic ask'),
        (166, 20, 1, 'dynamic_ask_delete_self', 'Delete its own ask for a dynamic ask');
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (1, 2),
        (1, 104),
        (1, 144),
        (2, 3),
        (2, 6),
        (2, 7),
        (2, 10),
        (2, 13),
        (2, 22),
        (2, 30),
        (2, 38),
        (2, 52),
        (2, 82),
        (2, 90),
        (2, 98),
        (2, 109),
        (2, 139),
        (2, 155),
        (2, 158),
        (2, 160),
        (2, 162),
        (2, 165),
        (3, 4),
        (3, 53),
        (3, 54),
        (3, 56),
        (3, 57),
        (3, 58),
        (3, 77),
        (3, 83),
        (3, 84),
        (3, 85),
        (3, 91),
        (3, 92),
        (4, 5),
        (4, 23),
        (4, 24),
        (4, 25),
        (4, 26),
        (4, 27),
        (4, 31),
        (4, 32),
        (4, 33),
        (4, 42),
        (4, 43),
        (4, 44),
        (4, 45),
        (4, 147),
        (4, 149),
        (4, 151),
        (4, 153),
        (4, 154),
        (4, 156),
        (4, 157),
        (4, 159),
        (4, 161),
        (4, 163),
        (4, 164),
        (4, 166),
        (5, 14),
        (5, 15),
        (5, 39),
        (5, 48),
        (5, 55),
        (5, 63),
        (5, 78),
        (5, 81),
        (5, 86),
        (5, 94),
        (5, 112),
        (5, 120),
        (5, 143),
        (5, 146),
        (6, 105),
        (6, 110),
        (6, 111),
        (6, 145),
        (7, 9),
        (7, 68),
        (8, 75),
        (9, 8),
        (9, 70),
        (9, 73),
        (9, 74),
        (9, 76),
        (9, 145),
        (10, 40),
        (10, 41),
        (10, 46),
        (10, 117),
        (10, 125),
        (10, 133),
        (10, 145),
        (10, 148),
        (10, 150),
        (10, 152),
        (11, 4),
        (11, 118),
        (11, 119),
        (12, 3),
        (12, 126),
        (12, 127),
        (13, 134),
        (13, 140),
        (13, 141),
        (13, 142),
        (13, 145),
        (22, 16),
        (22, 17),
        (22, 18),
        (22, 19),
        (22, 20),
        (22, 21),
        (23, 17),
        (24, 18),
        (25, 19),
        (26, 21),
        (30, 28),
        (30, 29),
        (31, 28),
        (32, 29),
        (38, 34),
        (38, 35),
        (38, 36),
        (38, 37),
        (38, 40),
        (38, 41),
        (42, 35),
        (43, 36),
        (44, 40),
        (45, 37),
        (52, 47),
        (52, 48),
        (52, 49),
        (52, 50),
        (52, 51),
        (68, 59),
        (68, 60),
        (68, 61),
        (68, 62),
        (68, 64),
        (68, 65),
        (68, 66),
        (68, 67),
        (70, 60),
        (73, 64),
        (74, 65),
        (75, 66),
        (76, 67),
        (82, 79),
        (82, 80),
        (83, 79),
        (84, 80),
        (90, 87),
        (90, 88),
        (90, 89),
        (91, 87),
        (92, 88),
        (98, 93),
        (98, 95),
        (98, 96),
        (98, 97),
        (104, 99),
        (104, 100),
        (104, 101),
        (104, 102),
        (104, 103),
        (109, 106),
        (109, 107),
        (109, 108),
        (110, 106),
        (111, 107),
        (117, 113),
        (117, 114),
        (117, 115),
        (117, 116),
        (118, 114),
        (119, 115),
        (125, 121),
        (125, 122),
        (125, 123),
        (125, 124),
        (126, 122),
        (127, 123),
        (133, 128),
        (133, 129),
        (133, 130),
        (133, 131),
        (133, 132),
        (139, 135),
        (139, 136),
        (139, 137),
        (139, 138),
        (140, 135),
        (141, 136),
        (142, 137),
        (147, 46),
        (149, 148),
        (151, 150),
        (153, 152),
        (156, 155),
        (159, 158),
        (163, 162),
        (166, 165);
        ');

        $this->addSql('
        INSERT INTO `user_auth_assignment` (`user_id`, `auth_item_id`) 
        SELECT user_id, role_id 
        FROM user_role
        ');

        $this->addSql('
        INSERT INTO `app_auth_item` (`app_id`, `auth_item_id`) 
        SELECT app_id, role_id 
        FROM app_role
        ');

        $this->addSql('DROP TABLE app_role');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_right');
        $this->addSql('DROP TABLE uright');
        $this->addSql('DROP TABLE user_right');
        $this->addSql('DROP TABLE user_role');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_auth_item DROP FOREIGN KEY FK_99529A9F5C4B72AD');
        $this->addSql('ALTER TABLE auth_item_child DROP FOREIGN KEY FK_1611424D727ACA70');
        $this->addSql('ALTER TABLE auth_item_child DROP FOREIGN KEY FK_1611424DDD62C21B');
        $this->addSql('ALTER TABLE user_auth_assignment DROP FOREIGN KEY FK_3C1C25815C4B72AD');
        $this->addSql('ALTER TABLE auth_item DROP FOREIGN KEY FK_313DC5AA3A6A23A2');
        $this->addSql('CREATE TABLE app_role (app_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_5247AFCAD60322AC (role_id), INDEX IDX_5247AFCA7987212D (app_id), PRIMARY KEY(app_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci, name VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_57698A6A727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role_right (role_id INT NOT NULL, right_id INT NOT NULL, INDEX IDX_43169D3B54976835 (right_id), INDEX IDX_43169D3BD60322AC (role_id), PRIMARY KEY(role_id, right_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE uright (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, description VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, object VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_right (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, right_id INT NOT NULL, territory_id INT DEFAULT NULL, INDEX IDX_56088E4C73F74AD4 (territory_id), INDEX IDX_56088E4C54976835 (right_id), INDEX IDX_56088E4CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_role (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, role_id INT NOT NULL, territory_id INT DEFAULT NULL, INDEX IDX_2DE8C6A373F74AD4 (territory_id), INDEX IDX_2DE8C6A3D60322AC (role_id), INDEX IDX_2DE8C6A3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE app_role ADD CONSTRAINT FK_5247AFCA7987212D FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_role ADD CONSTRAINT FK_5247AFCAD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A727ACA70 FOREIGN KEY (parent_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_right ADD CONSTRAINT FK_43169D3B54976835 FOREIGN KEY (right_id) REFERENCES uright (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_right ADD CONSTRAINT FK_43169D3BD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4C54976835 FOREIGN KEY (right_id) REFERENCES uright (id)');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4C73F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A373F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('DROP TABLE app_auth_item');
        $this->addSql('DROP TABLE auth_item');
        $this->addSql('DROP TABLE auth_item_child');
        $this->addSql('DROP TABLE auth_rule');
        $this->addSql('DROP TABLE user_auth_assignment');
    }
}
