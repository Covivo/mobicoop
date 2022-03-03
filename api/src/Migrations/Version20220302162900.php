<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration.
 */
final class Version20220302162900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // remove user_list, user_read items child to community_manager public and private
        $this->addSql('DELETE FROM `auth_item_child` WHERE `auth_item_child`.`parent_id` = 7 AND `auth_item_child`.`child_id` = 17');
        $this->addSql('DELETE FROM `auth_item_child` WHERE `auth_item_child`.`parent_id` = 7 AND `auth_item_child`.`child_id` = 20');
        $this->addSql('DELETE FROM `auth_item_child` WHERE `auth_item_child`.`parent_id` = 8 AND `auth_item_child`.`child_id` = 17');
        $this->addSql('DELETE FROM `auth_item_child` WHERE `auth_item_child`.`parent_id` = 8 AND `auth_item_child`.`child_id` = 20');
        $this->addSql('DELETE FROM `auth_item_child` WHERE `auth_item_child`.`parent_id` = 9 AND `auth_item_child`.`child_id` = 17');
        $this->addSql('DELETE FROM `auth_item_child` WHERE `auth_item_child`.`parent_id` = 9 AND `auth_item_child`.`child_id` = 20');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
