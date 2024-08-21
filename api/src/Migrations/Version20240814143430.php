<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240814143430 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('68', '69');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('68', '70');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('68', '71');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('68', '72');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('68', '73');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('68', '74');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('68', '75');");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES ('68', '76');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
