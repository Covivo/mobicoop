<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191129101000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (19, \'user_delete_account_was_passenger\', 1, 12);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (55, 19, 1, 1, 1, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (56, 19, 2, 1, 1, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (57, 19, 3, 1, 1, 1, 2);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (58, 19, 4, 1, 1, 1, 3);');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (20, \'user_delete_account_was_driver\', 1, 13);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (59, 20, 1, 1, 1, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (60, 20, 2, 1, 1, 1, 1);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (61, 20, 3, 1, 1, 1, 2);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (62, 20, 4, 1, 1, 1, 3);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 19;');
        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 20;');
        $this->addSql('DELETE FROM `action` WHERE `id` = 19;');
        $this->addSql('DELETE FROM `action` WHERE `id` = 20;');
    }
}
