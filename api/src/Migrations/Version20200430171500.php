<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration.
 */
final class Version20200430171500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // add solidary_transporter_schedule item
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (214, NULL, '1', 'solidary_transporters_schedule', 'Get a solidary transport schedule')
        ");

        // add solidary_transporter_schedule item child to solidary_manage
        $this->addSql("
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('133', '214')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
