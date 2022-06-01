<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration.
 */
final class Version20200520171800 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // add auth territory_read for ROLE_SOLIDARY_MANAGER and ROLE_ADMIN
        $this->addSql("
            INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '100'), ('2', '100')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
