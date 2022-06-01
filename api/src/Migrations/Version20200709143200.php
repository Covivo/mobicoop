<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration
 */
final class Version20200709143200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {

        // add community_restrict item child to ROLE_COMMUNITY_MANAGER_PUBLIC
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('8', '223')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
