<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220527145500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
            INSERT INTO `auth_item`(`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
                (306, NULL, 2, 'ROLE_TERRITORY_CONSULTANT', 'Territory consultant')
        ");

        $this->addSql('
            INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
                (306, 100),
                (306, 155),
                (306, 162),
                (306, 238),
                (306, 301)
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
