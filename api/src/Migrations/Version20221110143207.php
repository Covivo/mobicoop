<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Push token item migration.
 */
final class Version20221110143207 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
        DELETE FROM `auth_item_child` WHERE auth_item_child.parent_id = 7 and auth_item_child.child_id = 82
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
