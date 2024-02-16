<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240216141654 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `auth_item_child` WHERE `parent_id` = 8 AND `child_id` = 312;');
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (322, NULL, 2, 'ROLE_SUPER_COMMUNITY_MANAGER_PUBLIC', 'Community manager for public communities and can import users')
        ");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('322', '8');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('322', '312');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
