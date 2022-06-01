<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Push token item migration.
 */
final class Version20200428091000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
        INSERT INTO `auth_rule` (`id`, `name`) VALUES
        (22, \'PushTokenOwner\')
        ');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (212, 17, 1, 'push_token_create', 'Create a push token'),
        (213, 22, 1, 'push_token_delete', 'Delete a push token')
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (3, 212),
        (3, 213)
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
