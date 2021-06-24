<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate
 */
final class Version20210624114700 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // Add the test route for gamification (only admin)

        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (284, NULL, '1', 'gamification_test_action', 'To test a specific action to trigger logs and gamification actions');");

        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('2', '284');");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
