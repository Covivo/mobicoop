<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration
 */
final class Version20200515111000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // add solidary_transporter_schedule item
        $this->addSql("
            INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`)
            VALUES (223, NULL, '1', 'community_restrict', 'Display only communities user created')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
