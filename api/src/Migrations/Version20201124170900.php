<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * MyAd authorization
 */
final class Version20201124170900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (254, NULL, 1, 'external_journey_list', 'Make an external search');");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (255, NULL, 1, 'external_connection_create', 'Make an external connection');");

        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (5, 254)');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
