<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200309120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (5, "INSURANCE_POLICY", 1), (6, "HISTORY", 1), (7, "ACTORS", 1), (8, "SOLIDARY_CARPOOL", 1), (9, "BECOME_PARTNER", 1), (10, "FAQ", 1), (11, "TOOLBOX", 1), (12, "COMMUNITYINFOS", 1), (13, "LOM", 1), (14, "GOODPRACTICES", 1), (15, "MOREABOUT", 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 5;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 6;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 7;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 8;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 9;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 10;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 11;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 12;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 13;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 14;');
        $this->addSql('DELETE FROM `article` WHERE `article`.`id` = 15;');
    }
}
