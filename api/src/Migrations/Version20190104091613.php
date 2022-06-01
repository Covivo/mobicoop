<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190104091613 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE individual_stop (id INT AUTO_INCREMENT NOT NULL, proposal_id INT NOT NULL, address_id INT NOT NULL, position SMALLINT NOT NULL, delay INT NOT NULL, INDEX IDX_71948C05F4792058 (proposal_id), UNIQUE INDEX UNIQ_71948C05F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE car (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, brand VARCHAR(45) NOT NULL, model VARCHAR(45) NOT NULL, color VARCHAR(45) DEFAULT NULL, siv VARCHAR(45) DEFAULT NULL, seats INT NOT NULL, INDEX IDX_773DE69DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE direction (id INT AUTO_INCREMENT NOT NULL, distance INT NOT NULL, duration INT NOT NULL, ascend INT DEFAULT NULL, descend INT DEFAULT NULL, bbox_min_lon NUMERIC(10, 6) DEFAULT NULL, bbox_min_lat NUMERIC(10, 6) DEFAULT NULL, bbox_max_lon NUMERIC(10, 6) DEFAULT NULL, bbox_max_lat NUMERIC(10, 6) DEFAULT NULL, detail LONGTEXT NOT NULL, format VARCHAR(45) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE direction_zone (direction_id INT NOT NULL, zone_id INT NOT NULL, INDEX IDX_8890F4FAF73D997 (direction_id), INDEX IDX_8890F4F9F2C3FAB (zone_id), PRIMARY KEY(direction_id, zone_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptline (id INT AUTO_INCREMENT NOT NULL, ptcompany_id INT DEFAULT NULL, travel_mode_id INT DEFAULT NULL, name VARCHAR(45) NOT NULL, number VARCHAR(10) DEFAULT NULL, origin VARCHAR(100) NOT NULL, destination VARCHAR(100) NOT NULL, INDEX IDX_46E281876575144A (ptcompany_id), INDEX IDX_46E28187B6A6325B (travel_mode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptarrival (id INT AUTO_INCREMENT NOT NULL, address_id INT NOT NULL, individual_stop_id INT DEFAULT NULL, name VARCHAR(100) DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_F839CBDBF5B7AF75 (address_id), INDEX IDX_F839CBDBDE87B0 (individual_stop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptleg (id INT AUTO_INCREMENT NOT NULL, ptjourney_id INT DEFAULT NULL, ptdeparture_id INT DEFAULT NULL, ptarrival_id INT DEFAULT NULL, travel_mode_id INT DEFAULT NULL, ptline_id INT NOT NULL, indication LONGTEXT DEFAULT NULL, distance INT NOT NULL, duration INT NOT NULL, position INT NOT NULL, is_last TINYINT(1) NOT NULL, magnetic_direction VARCHAR(10) DEFAULT NULL, relative_direction VARCHAR(10) DEFAULT NULL, direction VARCHAR(45) DEFAULT NULL, INDEX IDX_513C7944272787F3 (ptjourney_id), INDEX IDX_513C79444D48F7F9 (ptdeparture_id), INDEX IDX_513C794490969994 (ptarrival_id), INDEX IDX_513C7944B6A6325B (travel_mode_id), INDEX IDX_513C7944EEA7E22D (ptline_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptcompany (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptdeparture (id INT AUTO_INCREMENT NOT NULL, address_id INT NOT NULL, individual_stop_id INT DEFAULT NULL, name VARCHAR(100) DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_C9E71422F5B7AF75 (address_id), INDEX IDX_C9E71422DE87B0 (individual_stop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptstep (id INT AUTO_INCREMENT NOT NULL, ptleg_id INT DEFAULT NULL, ptdeparture_id INT DEFAULT NULL, ptarrival_id INT DEFAULT NULL, distance INT NOT NULL, duration INT NOT NULL, position INT NOT NULL, is_last TINYINT(1) NOT NULL, magnetic_direction VARCHAR(10) DEFAULT NULL, relative_direction VARCHAR(10) DEFAULT NULL, INDEX IDX_D44FCB4DC0405E45 (ptleg_id), INDEX IDX_D44FCB4D4D48F7F9 (ptdeparture_id), INDEX IDX_D44FCB4D90969994 (ptarrival_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ptjourney (id INT AUTO_INCREMENT NOT NULL, ptdeparture_id INT NOT NULL, ptarrival_id INT NOT NULL, distance INT NOT NULL, duration INT NOT NULL, price NUMERIC(4, 2) DEFAULT NULL, co2 INT NOT NULL, change_number INT NOT NULL, INDEX IDX_6BCA51CD4D48F7F9 (ptdeparture_id), INDEX IDX_6BCA51CD90969994 (ptarrival_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE individual_stop ADD CONSTRAINT FK_71948C05F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE individual_stop ADD CONSTRAINT FK_71948C05F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE direction_zone ADD CONSTRAINT FK_8890F4FAF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE direction_zone ADD CONSTRAINT FK_8890F4F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptline ADD CONSTRAINT FK_46E281876575144A FOREIGN KEY (ptcompany_id) REFERENCES ptcompany (id)');
        $this->addSql('ALTER TABLE ptline ADD CONSTRAINT FK_46E28187B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE ptarrival ADD CONSTRAINT FK_F839CBDBF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE ptarrival ADD CONSTRAINT FK_F839CBDBDE87B0 FOREIGN KEY (individual_stop_id) REFERENCES individual_stop (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944272787F3 FOREIGN KEY (ptjourney_id) REFERENCES ptjourney (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C79444D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C794490969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944EEA7E22D FOREIGN KEY (ptline_id) REFERENCES ptline (id)');
        $this->addSql('ALTER TABLE ptdeparture ADD CONSTRAINT FK_C9E71422F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE ptdeparture ADD CONSTRAINT FK_C9E71422DE87B0 FOREIGN KEY (individual_stop_id) REFERENCES individual_stop (id)');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4DC0405E45 FOREIGN KEY (ptleg_id) REFERENCES ptleg (id)');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4D4D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id)');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4D90969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id)');
        $this->addSql('ALTER TABLE ptjourney ADD CONSTRAINT FK_6BCA51CD4D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id)');
        $this->addSql('ALTER TABLE ptjourney ADD CONSTRAINT FK_6BCA51CD90969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id)');
        $this->addSql('DROP TABLE route');
        $this->addSql('DROP TABLE user_address');
        $this->addSql('ALTER TABLE criteria ADD car_id INT DEFAULT NULL, ADD direction_driver_id INT DEFAULT NULL, ADD direction_passenger_id INT DEFAULT NULL, ADD ptjourney_id INT DEFAULT NULL, ADD max_deviation_time INT DEFAULT NULL, ADD max_deviation_distance INT DEFAULT NULL, ADD any_route_as_passenger TINYINT(1) NOT NULL, ADD multi_transport_mode TINYINT(1) DEFAULT NULL, CHANGE is_driver is_driver TINYINT(1) DEFAULT NULL, CHANGE is_passenger is_passenger TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81A862FD7E FOREIGN KEY (direction_driver_id) REFERENCES direction (id)');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B818044A959 FOREIGN KEY (direction_passenger_id) REFERENCES direction (id)');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81272787F3 FOREIGN KEY (ptjourney_id) REFERENCES ptjourney (id)');
        $this->addSql('CREATE INDEX IDX_B61F9B81C3C6F69F ON criteria (car_id)');
        $this->addSql('CREATE INDEX IDX_B61F9B81A862FD7E ON criteria (direction_driver_id)');
        $this->addSql('CREATE INDEX IDX_B61F9B818044A959 ON criteria (direction_passenger_id)');
        $this->addSql('CREATE INDEX IDX_B61F9B81272787F3 ON criteria (ptjourney_id)');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947263F6800A');
        $this->addSql('DROP INDEX IDX_BFE5947263F6800A ON proposal');
        $this->addSql('ALTER TABLE proposal DROP proposal_origin_id, DROP duration, DROP distance');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881B6A6325B');
        $this->addSql('DROP INDEX IDX_B3DC5881B6A6325B ON waypoint');
        $this->addSql('ALTER TABLE waypoint ADD ask_id INT DEFAULT NULL, CHANGE proposal_id proposal_id INT DEFAULT NULL, CHANGE travel_mode_id matching_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('CREATE INDEX IDX_B3DC5881B39876B8 ON waypoint (matching_id)');
        $this->addSql('CREATE INDEX IDX_B3DC5881B93F8B63 ON waypoint (ask_id)');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F2895D3561CC');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F2899C131AB7');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289AF6DAC90');
        $this->addSql('DROP INDEX IDX_DC10F289AF6DAC90 ON matching');
        $this->addSql('DROP INDEX IDX_DC10F2895D3561CC ON matching');
        $this->addSql('DROP INDEX IDX_DC10F2899C131AB7 ON matching');
        $this->addSql('ALTER TABLE matching DROP waypoint_offer_origin_id, DROP waypoint_offer_destination_id, DROP waypoint_request_origin_id, DROP duration, DROP distance');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE056A273CC');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0816C6140');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0B34B90EE');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0E5197E49');
        $this->addSql('DROP INDEX UNIQ_6826EAE056A273CC ON ask');
        $this->addSql('DROP INDEX UNIQ_6826EAE0816C6140 ON ask');
        $this->addSql('DROP INDEX IDX_6826EAE0B34B90EE ON ask');
        $this->addSql('DROP INDEX IDX_6826EAE0E5197E49 ON ask');
        $this->addSql('ALTER TABLE ask DROP origin_id, DROP destination_id, DROP user_offer_id, DROP user_request_id, DROP distance, DROP duration');
        $this->addSql('ALTER TABLE user ADD status SMALLINT NOT NULL, ADD any_route_as_passenger TINYINT(1) DEFAULT NULL, ADD multi_transport_mode TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD name VARCHAR(45) DEFAULT NULL, CHANGE street_address street_address VARCHAR(255) DEFAULT NULL, CHANGE address_locality address_locality VARCHAR(100) DEFAULT NULL, CHANGE address_country address_country VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ptarrival DROP FOREIGN KEY FK_F839CBDBDE87B0');
        $this->addSql('ALTER TABLE ptdeparture DROP FOREIGN KEY FK_C9E71422DE87B0');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81C3C6F69F');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81A862FD7E');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B818044A959');
        $this->addSql('ALTER TABLE direction_zone DROP FOREIGN KEY FK_8890F4FAF73D997');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C7944EEA7E22D');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C794490969994');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4D90969994');
        $this->addSql('ALTER TABLE ptjourney DROP FOREIGN KEY FK_6BCA51CD90969994');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4DC0405E45');
        $this->addSql('ALTER TABLE ptline DROP FOREIGN KEY FK_46E281876575144A');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C79444D48F7F9');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4D4D48F7F9');
        $this->addSql('ALTER TABLE ptjourney DROP FOREIGN KEY FK_6BCA51CD4D48F7F9');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81272787F3');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C7944272787F3');
        $this->addSql('CREATE TABLE route (id INT AUTO_INCREMENT NOT NULL, waypoint_origin_id INT NOT NULL, waypoint_destination_id INT NOT NULL, travel_mode_id INT DEFAULT NULL, detail LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, encode_format SMALLINT NOT NULL, distance INT DEFAULT NULL, duration INT DEFAULT NULL, UNIQUE INDEX UNIQ_2C420799B5C65EE (waypoint_origin_id), UNIQUE INDEX UNIQ_2C420798E11E2BE (waypoint_destination_id), INDEX IDX_2C42079B6A6325B (travel_mode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, address_id INT NOT NULL, name VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci, UNIQUE INDEX UNIQ_5543718B5E237E06A76ED395 (name, user_id), UNIQUE INDEX UNIQ_5543718BF5B7AF75 (address_id), INDEX IDX_5543718BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C420798E11E2BE FOREIGN KEY (waypoint_destination_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C420799B5C65EE FOREIGN KEY (waypoint_origin_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C42079B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718BF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE individual_stop');
        $this->addSql('DROP TABLE car');
        $this->addSql('DROP TABLE direction');
        $this->addSql('DROP TABLE direction_zone');
        $this->addSql('DROP TABLE ptline');
        $this->addSql('DROP TABLE ptarrival');
        $this->addSql('DROP TABLE ptleg');
        $this->addSql('DROP TABLE ptcompany');
        $this->addSql('DROP TABLE ptdeparture');
        $this->addSql('DROP TABLE ptstep');
        $this->addSql('DROP TABLE ptjourney');
        $this->addSql('ALTER TABLE address DROP name, CHANGE street_address street_address VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE address_locality address_locality VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE address_country address_country VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ask ADD origin_id INT NOT NULL, ADD destination_id INT NOT NULL, ADD user_offer_id INT NOT NULL, ADD user_request_id INT NOT NULL, ADD distance INT DEFAULT NULL, ADD duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE056A273CC FOREIGN KEY (origin_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0816C6140 FOREIGN KEY (destination_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0B34B90EE FOREIGN KEY (user_offer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0E5197E49 FOREIGN KEY (user_request_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6826EAE056A273CC ON ask (origin_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6826EAE0816C6140 ON ask (destination_id)');
        $this->addSql('CREATE INDEX IDX_6826EAE0B34B90EE ON ask (user_offer_id)');
        $this->addSql('CREATE INDEX IDX_6826EAE0E5197E49 ON ask (user_request_id)');
        $this->addSql('DROP INDEX IDX_B61F9B81C3C6F69F ON criteria');
        $this->addSql('DROP INDEX IDX_B61F9B81A862FD7E ON criteria');
        $this->addSql('DROP INDEX IDX_B61F9B818044A959 ON criteria');
        $this->addSql('DROP INDEX IDX_B61F9B81272787F3 ON criteria');
        $this->addSql('ALTER TABLE criteria DROP car_id, DROP direction_driver_id, DROP direction_passenger_id, DROP ptjourney_id, DROP max_deviation_time, DROP max_deviation_distance, DROP any_route_as_passenger, DROP multi_transport_mode, CHANGE is_driver is_driver TINYINT(1) NOT NULL, CHANGE is_passenger is_passenger TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE matching ADD waypoint_offer_origin_id INT DEFAULT NULL, ADD waypoint_offer_destination_id INT DEFAULT NULL, ADD waypoint_request_origin_id INT DEFAULT NULL, ADD duration INT DEFAULT NULL, ADD distance INT DEFAULT NULL');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F2895D3561CC FOREIGN KEY (waypoint_offer_destination_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F2899C131AB7 FOREIGN KEY (waypoint_request_origin_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289AF6DAC90 FOREIGN KEY (waypoint_offer_origin_id) REFERENCES waypoint (id)');
        $this->addSql('CREATE INDEX IDX_DC10F289AF6DAC90 ON matching (waypoint_offer_origin_id)');
        $this->addSql('CREATE INDEX IDX_DC10F2895D3561CC ON matching (waypoint_offer_destination_id)');
        $this->addSql('CREATE INDEX IDX_DC10F2899C131AB7 ON matching (waypoint_request_origin_id)');
        $this->addSql('ALTER TABLE proposal ADD proposal_origin_id INT DEFAULT NULL, ADD duration INT DEFAULT NULL, ADD distance INT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947263F6800A FOREIGN KEY (proposal_origin_id) REFERENCES proposal (id)');
        $this->addSql('CREATE INDEX IDX_BFE5947263F6800A ON proposal (proposal_origin_id)');
        $this->addSql('ALTER TABLE user DROP status, DROP any_route_as_passenger, DROP multi_transport_mode');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881B39876B8');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881B93F8B63');
        $this->addSql('DROP INDEX IDX_B3DC5881B39876B8 ON waypoint');
        $this->addSql('DROP INDEX IDX_B3DC5881B93F8B63 ON waypoint');
        $this->addSql('ALTER TABLE waypoint ADD travel_mode_id INT DEFAULT NULL, DROP matching_id, DROP ask_id, CHANGE proposal_id proposal_id INT NOT NULL');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('CREATE INDEX IDX_B3DC5881B6A6325B ON waypoint (travel_mode_id)');
    }
}
