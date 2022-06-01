<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200917170400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Everyone can list the solidary structures
        $this->addSql('UPDATE `auth_item_child` SET `parent_id` = \'171\' WHERE `auth_item_child`.`parent_id` = 11 AND `auth_item_child`.`child_id` = 118');
        $this->addSql('UPDATE `auth_item_child` SET `parent_id` = \'171\' WHERE `auth_item_child`.`parent_id` = 11 AND `auth_item_child`.`child_id` = 119');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
