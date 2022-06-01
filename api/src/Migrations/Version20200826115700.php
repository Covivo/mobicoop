<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration.
 */
final class Version20200826115700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // add solidary managers management items
        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (232, NULL, 1, 'solidary_manager_create', 'Create a solidary manager'),
        (233, NULL, 1, 'solidary_manager_read', 'Read the details of a solidary manager'),
        (234, NULL, 1, 'solidary_manager_update', 'Update a solidary manager'),
        (235, NULL, 1, 'solidary_manager_delete', 'Delete a solidary manager'),
        (236, NULL, 1, 'solidary_manager_list', 'List solidary managers'),
        (237, NULL, 1, 'solidary_manager_manage', 'Manage solidary managers')
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (237, 232),
        (237, 233),
        (237, 234),
        (237, 235),
        (237, 236),
        (2, 237),
        (10, 233)
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
