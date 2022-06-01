<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210527162600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `auth_rule` (`id`, `name`) VALUES ("32", "InteroperabilityUserCreator");');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = "32" WHERE `auth_item`.`id` = 259;');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = "32" WHERE `auth_item`.`id` = 260;');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = "32" WHERE `auth_item`.`id` = 261;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = NULL WHERE `auth_item`.`id` = 259;');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = NULL WHERE `auth_item`.`id` = 260;');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = NULL WHERE `auth_item`.`id` = 261;');
        $this->addSql('DELETE FROM `auth_rule` WHERE `auth_rule`.`id` = 32;');
    }
}
