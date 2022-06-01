<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Manual migration for actions and notification positions
 * Also add push notifications
 */
final class Version20191029141200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (12, \'carpool_canceled\', 1, 6);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (23, 12, 1, 1, 1, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (24, 12, 2, 1, 1, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (25, 12, 3, 1, 1, 1, 2);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (26, 12, 4, 1, 1, 1, 3);');

        $this->addSql('UPDATE `action` SET position=0 WHERE ID=4;');
        $this->addSql('UPDATE `action` SET position=1 WHERE ID=7;');
        $this->addSql('UPDATE `action` SET position=2 WHERE ID=8;');
        $this->addSql('UPDATE `action` SET position=3 WHERE ID=5;');
        $this->addSql('UPDATE `action` SET position=4 WHERE ID=6;');
        $this->addSql('UPDATE `action` SET position=5 WHERE ID=9;');

        $this->addSql('UPDATE `notification` SET position=0 WHERE ID=4;');
        $this->addSql('UPDATE `notification` SET position=1 WHERE ID=5;');
        $this->addSql('UPDATE `notification` SET position=2 WHERE ID=6;');
        $this->addSql('UPDATE `notification` SET position=0 WHERE ID=7;');
        $this->addSql('UPDATE `notification` SET position=1 WHERE ID=8;');
        $this->addSql('UPDATE `notification` SET position=2 WHERE ID=9;');
        $this->addSql('UPDATE `notification` SET position=0 WHERE ID=10;');
        $this->addSql('UPDATE `notification` SET position=1 WHERE ID=11;');
        $this->addSql('UPDATE `notification` SET position=2 WHERE ID=12;');
        $this->addSql('UPDATE `notification` SET position=0 WHERE ID=13;');
        $this->addSql('UPDATE `notification` SET position=1 WHERE ID=14;');
        $this->addSql('UPDATE `notification` SET position=0 WHERE ID=15;');
        $this->addSql('UPDATE `notification` SET position=1 WHERE ID=16;');
        $this->addSql('UPDATE `notification` SET position=2 WHERE ID=17;');
        $this->addSql('UPDATE `notification` SET position=0 WHERE ID=18;');
        $this->addSql('UPDATE `notification` SET position=1 WHERE ID=19;');
        $this->addSql('UPDATE `notification` SET position=2 WHERE ID=20;');

        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (27, 4, 4, 1, 1, 1, 3);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (28, 5, 4, 1, 1, 1, 3);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (29, 6, 4, 1, 1, 1, 3);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (30, 7, 4, 1, 1, 1, 3);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (31, 8, 4, 1, 1, 1, 3);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (32, 9, 4, 1, 1, 1, 3);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
