<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for #4766.
 */
final class Version20230614112400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Remove the article_manage right from the admin role
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=2 AND child_id=52');

        // Remove the editorial_manage right from the admin role
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=2 AND child_id=296');

        // add the article_manage right to the super admin role
        $this->addSql('INSERT INTO auth_item_child (parent_id, child_id) VALUES (1, 52)');

        // add the editorial_manage right to the super admin role
        $this->addSql('INSERT INTO auth_item_child (parent_id, child_id) VALUES (1, 296)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('INSERT INTO auth_item_child (parent_id, child_id) VALUES (2, 52), (2, 296)');

        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=1 AND child_id=52');
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=1 AND child_id=296');
    }
}
