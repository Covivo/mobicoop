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

        $this->addSql('INSERT INTO `auth_item`(`type`, `name`, `description`) VALUES (2, \'ROLE_TERRITORY_CONSULTANT\', \'Territory consultant\')');

        $this->addSql('INSERT INTO `auth_item_child`(`parent_id`, `child_id`)
            SELECT (SELECT ai.id FROM `auth_item` ai WHERE ai.name = \'ROLE_TERRITORY_CONSULTANT\'), ai2.id
            FROM `auth_item_child` aic
            LEFT JOIN `auth_item` ai ON ai.id = aic.parent_id
            LEFT JOIN `auth_item` ai2 ON ai2.id = aic.child_id
            WHERE ai2.name like \'%read%\'
            AND ai.id = 2');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
