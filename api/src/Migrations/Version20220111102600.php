<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Relaypoint type.
 */
final class Version20220111102600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `icon` (`id`, `private_icon_linked_id`, `name`, `file_name`) VALUES (24, NULL, 'private-relaypoint-rezopouce', 'private-relaypoint-rezopouce.svg');");

        $this->addSql("INSERT INTO `icon` (`id`, `private_icon_linked_id`, `name`, `file_name`) VALUES (25, 24, 'relaypoint-rezopouce', 'relaypoint-rezopouce.svg');");

        $this->addSql("INSERT INTO `relay_point_type` (`id`, `name`, `created_date`, `updated_date`, `icon_id`) VALUES ('12', 'Arrêt Rezo Pouce', '2022-01-11 09:39:13.000000', NULL, '25');");

        //Aire-Covoiturage
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '15' WHERE `relay_point_type`.`id` = 1;");
        //P+R
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '19' WHERE `relay_point_type`.`id` = 2;");
        //Gare
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '22' WHERE `relay_point_type`.`id` = 3;");
        //Parking
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '20' WHERE `relay_point_type`.`id` = 4;");
        //Zone d'activité
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '1' WHERE `relay_point_type`.`id` = 5;");
        //Aucun
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '1' WHERE `relay_point_type`.`id` = 6;");
        //Arrêt
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '14' WHERE `relay_point_type`.`id` = 7;");
        //Bâtiment
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '23' WHERE `relay_point_type`.`id` = 8;");
        //Taxi
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '12' WHERE `relay_point_type`.`id` = 9;");
        //Communauté
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '3' WHERE `relay_point_type`.`id` = 10;");
        //Arrêt-covoiturage
        $this->addSql("UPDATE `relay_point_type` SET `icon_id` = '17' WHERE `relay_point_type`.`id` = 11;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
