<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230622104600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (311, NULL, '1', 'driver_licence_number_validation', 'Validate a driver licence number');");

        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('5', '311');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
