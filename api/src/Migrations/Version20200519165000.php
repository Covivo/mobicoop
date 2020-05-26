<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration
 */
final class Version20200519165000 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // add access admin for ROLE_SOLIDARY_MANAGER
        $this->addSql("
            INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('10', '145')
        ");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
