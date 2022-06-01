<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200907121000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // add dashboard access
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (239, NULL, 1, 'block_create', 'Create a Block. Block or unblock a User')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (240, NULL, 1, 'block_blocked', 'List the Users blocked by a specific User')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (241, NULL, 1, 'block_blockedby', 'List the Users currently blocking a specific User')");

        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 239)");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 240)");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 241)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
