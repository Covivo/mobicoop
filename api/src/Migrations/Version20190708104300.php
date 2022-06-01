<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190708104300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // insert media
        $this->addSql('INSERT INTO `medium` (`id`, `name`) VALUES (1, \'Internal message\');');
        $this->addSql('INSERT INTO `medium` (`id`, `name`) VALUES (2, \'Email\');');
        $this->addSql('INSERT INTO `medium` (`id`, `name`) VALUES (3, \'Sms\');');
        $this->addSql('INSERT INTO `medium` (`id`, `name`) VALUES (4, \'Push\');');

        // insert actions
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (1, \'user_registered\', \'User register\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (2, \'user_password_change_asked\', \'User asked to change its password\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (3, \'user_password_changed\', \'User password changed\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (4, \'carpool_ask_posted\', \'Ask posted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (5, \'carpool_ask_accepted\', \'Ask accepted\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (6, \'carpool_ask_refused\', \'Ask refused\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (7, \'communication_internal_message_received\', \'Internal message received\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (8, \'carpool_matching_new\', \'New carpool matchings\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (9, \'carpool_ad_renewal\', \'Ad renewal\');');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `uname`) VALUES (10, \'user_updated_self\', \'User updated its profile\');');

        // insert notifications
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (1, 1, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (2, 2, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (3, 3, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (4, 4, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (5, 4, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (6, 4, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (7, 5, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (8, 5, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (9, 5, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (10, 6, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (11, 6, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (12, 6, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (13, 7, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (14, 7, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (15, 8, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (16, 8, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (17, 8, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (18, 9, 1, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (19, 9, 2, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (20, 9, 3, null, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template`, `active`, `active_default`) VALUES (21, 10, 1, null, 1, 1);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `medium` WHERE `id` IN (1,2,3,4);');
        $this->addSql('DELETE FROM `action` WHERE `id` IN (1,2,3,4,,5,6,7,8,9,10);');
        $this->addSql('DELETE FROM `notification` WHERE `id` IN (1,2,3,4,,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21);');
    }
}
