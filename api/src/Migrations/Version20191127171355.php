<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127171355 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE `notification` SET `active` = '0' WHERE `notification`.`id` = 22;");
        $this->addSql("UPDATE `notification` SET `active` = '0' WHERE `notification`.`id` = 23;");
        $this->addSql("UPDATE `notification` SET `active` = '0' WHERE `notification`.`id` = 24;");
        $this->addSql("UPDATE `notification` SET `active` = '0' WHERE `notification`.`id` = 25;");
        $this->addSql("UPDATE `notification` SET `active` = '0' WHERE `notification`.`id` = 26;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 22;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 23;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 24;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 25;");
        $this->addSql("UPDATE `notification` SET `active` = '1' WHERE `notification`.`id` = 26;");
    }
}
