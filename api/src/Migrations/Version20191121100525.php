<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191121100525 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("INSERT INTO `icon` (`id`, `private_icon_linked_id`, `name`, `file_name`) VALUES (1, NULL,'address-any', 'address-any.svg'), (2, NULL, 'address-personal', 'address-personal.svg'), (3, NULL,'community', 'community.svg'), (4, NULL, 'event', 'event.svg'), (5, NULL, 'private-relaypoint-bus', 'private-relaypoint-bus.svg'), (6, NULL, 'private-relaypoint-carpool-area', 'private-relaypoint-carpool-area.svg'), (7, NULL, 'private-relaypoint-company', 'private-relaypoint-company.svg'), (8, NULL, 'private-relaypoint-carpool-stop', 'private-relaypoint-carpool-stop.svg'), (9, NULL, 'private-relaypoint-drop-off-area', 'private-relaypoint-drop-off-area.svg'), (10, NULL, 'private-relaypoint-park-and-ride', 'private-relaypoint-park-and-ride.svg'), (11, NULL, 'private-relaypoint-parking', 'private-relaypoint-parking.svg'), (12, NULL, 'private-relaypoint-taxi', 'private-relaypoint-taxi.svg'), (13, NULL, 'private-relaypoint-train', 'private-relaypoint-train.svg'), (14, 5, 'relaypoint-bus', 'relaypoint-bus.svg'), (15, 6, 'relaypoint-carpool-area', 'relaypoint-carpool-area.svg'), (16, 7, 'relaypoint-company', 'relaypoint-company.svg'), (17, 8, 'relaypoint-carpool-stop', 'relaypoint-carpool-stop.svg'), (18, 9, 'relaypoint-drop-off-area', 'relaypoint-drop-off-area.svg'), (19, 10, 'relaypoint-park-and-ride', 'relaypoint-park-and-ride.svg'), (20, 11, 'relaypoint-parking', 'relaypoint-parking.svg'), (21, 12, 'relaypoint-taxi', 'relaypoint-taxi.svg'), (22, 13,'relaypoint-train', 'relaypoint-train.svg'), (23, NULL, 'venue', 'venue.svg');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 1;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 2;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 3;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 4;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 5;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 6;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 7;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 8;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 9;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 10;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 11;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 12;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 13;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 14;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 15;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 16;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 17;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 18;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 19;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 20;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 21;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 22;');
        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 23;');
    }
}
