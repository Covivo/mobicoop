<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate
 */
final class Version20210130100300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `auth_item` SET `auth_rule_id` = NULL WHERE `auth_item`.`id` = 228");
        $this->addSql("UPDATE `auth_item_child` SET `parent_id` = '5' WHERE `auth_item_child`.`parent_id` = 4 AND `auth_item_child`.`child_id` = 228");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
