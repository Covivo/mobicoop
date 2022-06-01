<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200129091826 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (22, \'user_registered_delegate\', 0, 0);');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `in_log`, `position`) VALUES (23, \'user_registered_delegate_password_send\', 0, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (64, 22, 2, 1, 1, 0, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (65, 23, 3, 1, 1, 0, 0);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 22;');
        $this->addSql('DELETE FROM `notification` WHERE `action_id` = 23;');
        $this->addSql('DELETE FROM `action` WHERE `id` = 64;');
        $this->addSql('DELETE FROM `action` WHERE `id` = 65;');
    }
}
