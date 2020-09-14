<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200914112900 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // add electronic payements rights

        $this->addSql('
        INSERT INTO `auth_rule` (`id`, `name`) VALUES
        (29, \'PaymentAuthor\')
        ');

        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (242, 29, 1, 'electronic_payment_create', 'Create (make) an electronic payment')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (243, 29, 1, 'electronic_payment_read', 'Read an electronic payment')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (244, NULL, 1, 'electronic_payment_list', 'List electronic payments')");

        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 242)");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 243)");
        $this->addSql("INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 244)");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
