<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration
 */
final class Version20200514112400 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {

        // Add user_list, user_read items child to community_manager public and private
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('5', '51')");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
