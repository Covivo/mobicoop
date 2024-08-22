<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240814143430 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `auth_rule` (`id`, `name`) VALUES
        (33, \'CommunityManagerSelf\');
        ');

        $this->addSql('INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (323, 33, 1, \'community_manage_self\', \'Community manager can manage its own communities\');
        ');

        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=8 AND child_id=7');
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=9 AND child_id=7');
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=8 AND child_id=68');
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=9 AND child_id=68');

        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (8, 323);');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (9, 323);');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (323, 68);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
