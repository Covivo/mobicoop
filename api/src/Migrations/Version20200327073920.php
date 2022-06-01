<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200327073920 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (203, 15, 1, 'carpool_proof_create', 'Create a carpool proof for an ad'),
        (204, NULL, 1, 'carpool_proof_read', 'Read an carpool proof'),
        (205, 15, 1, 'carpool_proof_read_self', 'Read its own carpool proof'),
        (206, 15, 1, 'carpool_proof_update', 'Update a carpool proof'),
        (207, 15, 1, 'dynamic_proof_create', 'Create a dynamic carpool proof for an ad'),
        (208, NULL, 1, 'dynamic_proof_read', 'Read a dynamic carpool proof'),
        (209, 15, 1, 'dynamic_proof_read_self', 'Read its own dynamic carpool proof'),
        (210, 15, 1, 'dynamic_proof_update', 'Update a dynamic carpool proof');
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (4, 203),
        (38, 204),
        (4, 205),
        (205, 204),
        (4, 206),
        (4, 207),
        (38, 208),
        (4, 209),
        (209, 208),
        (4, 210);
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
