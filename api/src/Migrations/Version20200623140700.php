<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200623140700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
        INSERT INTO `auth_rule` (`id`, `name`) VALUES 
        ('24', 'AdExternal');");

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES 
        ('227', '4', '1', 'ad_read_self', 'Read its own ad'),
        ('228', '24', '1', 'ad_read_external', 'Read ad from external');");

        $this->addSql("
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES 
        ('227', '34'), 
        ('4', '227'),
        ('228', '34'), 
        ('4', '228');
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
