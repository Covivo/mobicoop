<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201027163000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // add dashboard access
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (242, NULL, 1, 'report_create', 'Report a User/Event...')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (243, NULL, 1, 'report_read', 'View a Report')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (244, NULL, 1, 'report_update', 'Update a Report')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (245, NULL, 1, 'report_delete', 'Delete a Report')");

        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (5, 242)');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (5, 243)');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (5, 244)');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (5, 245)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
