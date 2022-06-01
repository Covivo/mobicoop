<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Community manager mass mailing => fix Version20200923155100
 */
final class Version20201102095500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM `auth_item_child` where `parent_id` = 8 and `child_id` = 105;');
        $this->addSql('DELETE FROM `auth_item_child` where `parent_id` = 8 and `child_id` = 110;');
        $this->addSql('DELETE FROM `auth_item_child` where `parent_id` = 8 and `child_id` = 111;');

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (8, 134),
        (8, 140),
        (8, 141),
        (8, 142)
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
