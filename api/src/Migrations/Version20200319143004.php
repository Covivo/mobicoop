<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200319143004 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_user_structure ADD status INT DEFAULT 0 NOT NULL, ADD accepted_date DATETIME DEFAULT NULL, ADD refused_date DATETIME DEFAULT NULL');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (171, NULL, 2, 'ROLE_SOLIDARY_VOLUNTEER_CANDIDATE', 'Solidary volunteer candidate'),
        (172, NULL, 2, 'ROLE_SOLIDARY_BENEFICIARY_CANDIDATE', 'Solidary beneficiary candidate');
        ");

        $this->addSql('UPDATE auth_item_child set parent_id=171 WHERE auth_item_child.parent_id=11 AND auth_item_child.child_id = 168');
        $this->addSql('UPDATE auth_item_child set parent_id=172 WHERE auth_item_child.parent_id=12 AND auth_item_child.child_id = 170');

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (11, 171),
        (12, 172);
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_user_structure DROP status, DROP accepted_date, DROP refused_date');
    }
}
