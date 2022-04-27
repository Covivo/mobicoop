<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Push token item migration.
 */
final class Version20220427174956 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
        INSERT INTO `auth_rule` (`id`, `name`) VALUES
        (33, \'CommunityManagerCanManageEvents\')
        ');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (306, 33, 1, 'event_manage_community_manager', 'A community manager can manage events')
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (7, 306),
        (306, 79),
        (306, 80)
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
