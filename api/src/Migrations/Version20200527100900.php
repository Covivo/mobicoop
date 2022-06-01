<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * App versioning read migration
 */
final class Version20200527100900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // add solidary_transporter_schedule item
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (225, NULL, '1', 'app_versioning_read', 'Read app versioning informations')
        ");
        $this->addSql("
            INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('5', '225')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
