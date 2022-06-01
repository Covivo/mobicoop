<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210720103548 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (291, NULL, 1, 'editorial_create', 'Create an editorial'),
        (292, NULL, 1, 'editorial_read', 'Read an editorial'),
        (293, NULL, 1, 'editorial_update', 'Update an editorial'),
        (294, NULL, 1, 'editorial_delete', 'Delete an editorial'),
        (295, NULL, 1, 'editorial_list', 'View the list of editorials'),
        (296, NULL, 1, 'editorial_manage', 'Manage editorials')
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (296, 291),
        (296, 292),
        (296, 293),
        (296, 294),
        (296, 295),
        (2, 296),
        (5, 292),
        (5, 295)
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
