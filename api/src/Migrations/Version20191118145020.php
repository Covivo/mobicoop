<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191118145020 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (14, \'carpool_ask_linked_ad_deleted\', 1, 7);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (34, 14, 1, 1, 1, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (35, 14, 2, 1, 1, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (36, 14, 3, 1, 1, 1, 2);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (37, 14, 4, 1, 1, 1, 3);');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (15, \'passenger_carpool_linked_ad_deleted\', 1, 8);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (38, 15, 1, 1, 1, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (39, 15, 2, 1, 1, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (40, 15, 3, 1, 1, 1, 2);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (41, 15, 4, 1, 1, 1, 3);');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (16, \'passenger_carpool_linked_ad_deleted_urgent\', 1, 9);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (42, 16, 1, 1, 1, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (43, 16, 2, 1, 1, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (44, 16, 3, 1, 1, 1, 2);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (45, 16, 4, 1, 1, 1, 3);');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (17, \'driver_carpool_linked_ad_deleted\', 1, 10);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (46, 17, 1, 1, 1, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (47, 17, 2, 1, 1, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (48, 17, 3, 1, 1, 1, 2);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (49, 17, 4, 1, 1, 1, 3);');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (18, \'driver_carpool_linked_ad_deleted_urgent\', 1, 11);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (50, 18, 1, 1, 1, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (52, 18, 2, 1, 1, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (53, 18, 3, 1, 1, 1, 2);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (54, 18, 4, 1, 1, 1, 3);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 14;');
        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 15;');
        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 16;');
        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 17;');
        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 18;');

        $this->addSql('DELETE FROM `action` WHERE `id` = 14;');
        $this->addSql('DELETE FROM `action` WHERE `id` = 15;');
        $this->addSql('DELETE FROM `action` WHERE `id` = 16;');
        $this->addSql('DELETE FROM `action` WHERE `id` = 17;');
        $this->addSql('DELETE FROM `action` WHERE `id` = 18;');
    }
}
