<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration
 */
final class Version20200514112400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {

        // Add article_list items child to role_user
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('5', '51')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
