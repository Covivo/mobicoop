<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add BankAccount related rights.
 */
final class Version20200720111000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES 
        ('229', NULL, '1', 'bank_account_create', 'Create a bank account'),
        ('230', NULL, '1', 'bank_account_list', 'List bank accounts'),
        ('231', NULL, '1', 'bank_account_disable', 'Disable a bank account');");

        $this->addSql("
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES 
        ('3', '229'), 
        ('3', '230'),
        ('3', '231');
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
