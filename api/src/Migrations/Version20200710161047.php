<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710161047 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
        INSERT INTO `auth_rule` (`id`, `name`) VALUES
        (25, \'CommunityAvailable\'),
        (26, \'EventAvailable\'),
        (27, \'CampaignAvailable\'),
        (28, \'SolidaryAvailable\');
        ');
        $this->addSql("UPDATE `auth_item` SET `auth_rule_id` = '25' WHERE `auth_item`.`id` = 68;");
        $this->addSql("UPDATE `auth_item` SET `auth_rule_id` = '26' WHERE `auth_item`.`id` = 82;");
        $this->addSql("UPDATE `auth_item` SET `auth_rule_id` = '27' WHERE `auth_item`.`id` = 139;");
        $this->addSql("UPDATE `auth_item` SET `auth_rule_id` = '28' WHERE `auth_item`.`id` = 133;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = NULL WHERE `auth_item`.`id` = 68;');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = NULL WHERE `auth_item`.`id` = 82;');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = NULL WHERE `auth_item`.`id` = 139;');
        $this->addSql('UPDATE `auth_item` SET `auth_rule_id` = NULL WHERE `auth_item`.`id` = 133;');
        $this->addSql('DELETE FROM `auth_rule` WHERE `auth_rule`.`id` = 25;');
        $this->addSql('DELETE FROM `auth_rule` WHERE `auth_rule`.`id` = 26;');
        $this->addSql('DELETE FROM `auth_rule` WHERE `auth_rule`.`id` = 27;');
        $this->addSql('DELETE FROM `auth_rule` WHERE `auth_rule`.`id` = 28;');
    }
}
