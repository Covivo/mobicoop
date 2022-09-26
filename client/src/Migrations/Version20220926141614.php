<?php

declare(strict_types=1);

// namespace DoctrineMigrations; // For dev

namespace App\Migrations; // For test/prod

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Evol #4205.
 */
final class Version20220926141614 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
        $this->addSql("UPDATE `notification` SET `alt` = '1' WHERE `medium_id` = 2 AND `alt` IS NULL;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 139;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 140;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 141;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 142;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 143;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 144;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 145;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 146;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 147;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 148;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 149;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 150;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
    }
}
