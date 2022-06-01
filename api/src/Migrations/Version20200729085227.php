<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration
 */
final class Version20200729085227 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Add community_manager to community_manager public
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('8', '7')");

        // Add community_restrict to community_manager private
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('9', '223')");

        // Add community_manager private to community_manager
        $this->addSql('UPDATE auth_item_child set parent_id=\'9\', child_id=\'7\' WHERE parent_id=\'7\' AND child_id=\'9\'');

        // Delete community_manager public to community_manager private
        $this->addSql('DELETE FROM `auth_item_child` WHERE parent_id=\'9\' AND child_id=\'8\'');

        // Link between community_manager public and community_restrict already exists (parent_id = 8 - child_id = 223)
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
