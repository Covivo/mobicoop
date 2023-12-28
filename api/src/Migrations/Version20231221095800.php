<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration.
 */
final class Version20231221095800 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // #7094 - create hitchhiking roles

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (318, NULL, 2, 'ROLE_HITCHHIKING_WATCHER', 'List and read hitchhiking users'),
        (319, NULL, 2, 'ROLE_HITCHHIKING_MANAGER', 'List, read, create and edit hitchiking users'),
        (320, NULL, 2, 'ROLE_HITCHHIKING_ADMINISTRATOR', 'All operations related to hitchhiking including identity proof validation')
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (318, 309),
        (319, 318),
        (319, 308),
        (320, 319),
        (320, 304),
        (1, 320)
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
