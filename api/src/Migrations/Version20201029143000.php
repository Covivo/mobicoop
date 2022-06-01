<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201029143000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Add review
        $this->addSql("INSERT INTO `auth_rule` (`id`, `name`) VALUES ('29', 'ReviewAuthor')");

        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (246, 29, 1, 'review_create', 'Create (leave) a Review on someone')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (247, NULL, 1, 'review_read', 'Read a review of someone')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (248, NULL, 1, 'review_update', 'Update a review')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (249, NULL, 1, 'review_delete', 'Delete a review')");
        $this->addSql("INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES (250, NULL, 1, 'review_list', 'List the reviews where the calling user is involved')");

        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 246)');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 247)');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (2, 248)');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (2, 249)');
        $this->addSql('INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES (3, 250)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
