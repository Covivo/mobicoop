<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190806091803 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (11, \'carpool_proposal_posted\', \'User Publish a proposal\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (12, \'driver_carpool_ask_posted\', \'Ask posted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (13,\'driver_carpool_ask_accepted\', \'Ask accepted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (14, \'driver_carpool_ask_refused\', \'Ask refused\');');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (15, \'passenger_carpool_ask_posted\', \'Ask posted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (16, \'passenger_carpool_ask_accepted\', \'Ask accepted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (17, \'passenger_carpool_ask_refused\', \'Ask refused\');');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (18, \'driver_carpool_ask_posted_regular\', \'Ask posted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (19, \'driver_carpool_ask_accepted_regular\', \'Ask accepted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (20, \'driver_carpool_ask_refused_regular\', \'Ask refused\');');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (21, \'passenger_carpool_ask_posted_regular\', \'Ask posted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (22, \'passenger_carpool_ask_accepted_regular\', \'Ask accepted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (23, \'passenger_carpool_ask_refused_regular\', \'Ask refused\');');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (24, \'driver_carpool_ask_updated\', \'Ask posted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (25, \'passenger_carpool_ask_updated\', \'Ask posted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (26, \'driver_carpool_ask_updated_regular\', \'Ask posted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (27, \'passenger_carpool_ask_updated_regular\', \'Ask posted\');');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (28, \'driver_carpool_matching_new\', \'New carpool matchings\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (29, \'driver_carpool_matching_new_regular\', \'New carpool matchings\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (30, \'passenger_carpool_matching_new\', \'New carpool matchings\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (31, \'passenger_carpool_matching_new_regular\', \'New carpool matchings\');');


        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (22, 11, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (23, 12, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (24, 13, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (25, 14, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (26, 15, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (27, 16, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (28, 17, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (29, 18, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (30, 19, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (31, 20, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (32, 21, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (33, 22, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (34, 23, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (35, 24, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (36, 25, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (37, 26, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (38, 27, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (39, 28, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (40, 29, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (41, 30, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (42, 31, 2, null, 1, 1);');

        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (43, 12, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (44, 13, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (45, 14, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (46, 15, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (47, 16, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (48, 17, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (49, 18, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (50, 19, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (51, 20, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (52, 21, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (53, 22, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (54, 23, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (55, 24, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (56, 25, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (57, 26, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (58, 27, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (59, 28, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (60, 29, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (61, 30, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (62, 31, 1, null, 1, 1);');

        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (63, 12, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (64, 13, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (65, 14, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (66, 15, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (67, 16, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (68, 17, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (69, 18, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (70, 19, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (71, 20, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (72, 21, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (73, 22, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (74, 23, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (75, 24, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (76, 25, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (77, 26, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (78, 27, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (79, 28, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (80, 29, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (81, 30, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `active_default`) VALUES (82, 31, 3, null, 1, 1);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `action` WHERE `id` >= 11 and `id` <= 31');
        $this->addSql('DELETE FROM `notification` WHERE `id` >= 22 and `id` <= 82');
    }
}
