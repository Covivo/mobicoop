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
 **************************/

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181213161451 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F28985C70859');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F28993EF35BA');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289A54D0C13');
        $this->addSql('ALTER TABLE path DROP FOREIGN KEY FK_B548B0FEE74C799');
        $this->addSql('ALTER TABLE path DROP FOREIGN KEY FK_B548B0FFCC16877');
        $this->addSql('ALTER TABLE solicitation DROP FOREIGN KEY FK_4FA96783293B97A1');
        $this->addSql('CREATE TABLE waypoint (id INT AUTO_INCREMENT NOT NULL, proposal_id INT NOT NULL, address_id INT NOT NULL, travel_mode_id INT DEFAULT NULL, position SMALLINT NOT NULL, is_destination TINYINT(1) NOT NULL, INDEX IDX_B3DC5881F4792058 (proposal_id), UNIQUE INDEX UNIQ_B3DC5881F5B7AF75 (address_id), INDEX IDX_B3DC5881B6A6325B (travel_mode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ask (id INT AUTO_INCREMENT NOT NULL, origin_id INT NOT NULL, destination_id INT NOT NULL, user_id INT NOT NULL, user_offer_id INT NOT NULL, user_request_id INT NOT NULL, matching_id INT NOT NULL, ask_linked_id INT DEFAULT NULL, criteria_id INT NOT NULL, status SMALLINT NOT NULL, type SMALLINT NOT NULL, created_date DATETIME NOT NULL, distance INT DEFAULT NULL, duration INT DEFAULT NULL, UNIQUE INDEX UNIQ_6826EAE056A273CC (origin_id), UNIQUE INDEX UNIQ_6826EAE0816C6140 (destination_id), INDEX IDX_6826EAE0A76ED395 (user_id), INDEX IDX_6826EAE0B34B90EE (user_offer_id), INDEX IDX_6826EAE0E5197E49 (user_request_id), INDEX IDX_6826EAE0B39876B8 (matching_id), UNIQUE INDEX UNIQ_6826EAE04F05FAE (ask_linked_id), UNIQUE INDEX UNIQ_6826EAE0990BEA15 (criteria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE route (id INT AUTO_INCREMENT NOT NULL, waypoint_origin_id INT NOT NULL, waypoint_destination_id INT NOT NULL, travel_mode_id INT DEFAULT NULL, detail LONGTEXT NOT NULL, encode_format SMALLINT NOT NULL, distance INT DEFAULT NULL, duration INT DEFAULT NULL, UNIQUE INDEX UNIQ_2C420799B5C65EE (waypoint_origin_id), UNIQUE INDEX UNIQ_2C420798E11E2BE (waypoint_destination_id), INDEX IDX_2C42079B6A6325B (travel_mode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE056A273CC FOREIGN KEY (origin_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0816C6140 FOREIGN KEY (destination_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0B34B90EE FOREIGN KEY (user_offer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0E5197E49 FOREIGN KEY (user_request_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE04F05FAE FOREIGN KEY (ask_linked_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C420799B5C65EE FOREIGN KEY (waypoint_origin_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C420798E11E2BE FOREIGN KEY (waypoint_destination_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE route ADD CONSTRAINT FK_2C42079B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('DROP TABLE path');
        $this->addSql('DROP TABLE point');
        $this->addSql('DROP TABLE solicitation');
        $this->addSql('ALTER TABLE criteria ADD is_driver TINYINT(1) NOT NULL, ADD is_passenger TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472558C41CB');
        $this->addSql('DROP INDEX UNIQ_BFE59472558C41CB ON proposal');
        $this->addSql('ALTER TABLE proposal ADD type SMALLINT NOT NULL, ADD distance INT DEFAULT NULL, DROP proposal_linked_journey_id, DROP proposal_type, DROP journey_type, DROP distance_real, DROP distance_fly, DROP cape');
        $this->addSql('DROP INDEX IDX_DC10F28993EF35BA ON matching');
        $this->addSql('DROP INDEX IDX_DC10F28985C70859 ON matching');
        $this->addSql('DROP INDEX IDX_DC10F289A54D0C13 ON matching');
        $this->addSql('ALTER TABLE matching ADD waypoint_offer_origin_id INT DEFAULT NULL, ADD waypoint_offer_destination_id INT DEFAULT NULL, ADD waypoint_request_origin_id INT DEFAULT NULL, ADD distance INT DEFAULT NULL, DROP point_offer_from_id, DROP point_offer_to_id, DROP point_request_from_id, DROP distance_real, DROP distance_fly');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289AF6DAC90 FOREIGN KEY (waypoint_offer_origin_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F2895D3561CC FOREIGN KEY (waypoint_offer_destination_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F2899C131AB7 FOREIGN KEY (waypoint_request_origin_id) REFERENCES waypoint (id)');
        $this->addSql('CREATE INDEX IDX_DC10F289AF6DAC90 ON matching (waypoint_offer_origin_id)');
        $this->addSql('CREATE INDEX IDX_DC10F2895D3561CC ON matching (waypoint_offer_destination_id)');
        $this->addSql('CREATE INDEX IDX_DC10F2899C131AB7 ON matching (waypoint_request_origin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289AF6DAC90');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F2895D3561CC');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F2899C131AB7');
        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C420799B5C65EE');
        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C420798E11E2BE');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE04F05FAE');
        $this->addSql('CREATE TABLE path (id INT AUTO_INCREMENT NOT NULL, point1_id INT NOT NULL, point2_id INT NOT NULL, travel_mode_id INT DEFAULT NULL, position INT NOT NULL, detail LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, encode_format SMALLINT NOT NULL, UNIQUE INDEX UNIQ_B548B0FEE74C799 (point1_id), UNIQUE INDEX UNIQ_B548B0FFCC16877 (point2_id), INDEX IDX_B548B0FB6A6325B (travel_mode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE point (id INT AUTO_INCREMENT NOT NULL, proposal_id INT NOT NULL, address_id INT NOT NULL, travel_mode_id INT DEFAULT NULL, position SMALLINT NOT NULL, last_point TINYINT(1) NOT NULL, distance_next_real INT DEFAULT NULL, distance_next_fly INT DEFAULT NULL, duration_next INT DEFAULT NULL, UNIQUE INDEX UNIQ_B7A5F324F5B7AF75 (address_id), INDEX IDX_B7A5F324F4792058 (proposal_id), INDEX IDX_B7A5F324B6A6325B (travel_mode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solicitation (id INT AUTO_INCREMENT NOT NULL, address_from_id INT NOT NULL, address_to_id INT NOT NULL, user_id INT NOT NULL, user_offer_id INT NOT NULL, user_request_id INT NOT NULL, matching_id INT NOT NULL, criteria_id INT NOT NULL, solicitation_linked_id INT DEFAULT NULL, status SMALLINT NOT NULL, journey_type SMALLINT NOT NULL, created_date DATETIME NOT NULL, distance_real INT DEFAULT NULL, distance_fly INT DEFAULT NULL, duration INT DEFAULT NULL, UNIQUE INDEX UNIQ_4FA96783990BEA15 (criteria_id), UNIQUE INDEX UNIQ_4FA96783232B2E93 (address_from_id), UNIQUE INDEX UNIQ_4FA967837903D45 (address_to_id), UNIQUE INDEX UNIQ_4FA96783293B97A1 (solicitation_linked_id), INDEX IDX_4FA96783A76ED395 (user_id), INDEX IDX_4FA96783B34B90EE (user_offer_id), INDEX IDX_4FA96783E5197E49 (user_request_id), INDEX IDX_4FA96783B39876B8 (matching_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE path ADD CONSTRAINT FK_B548B0FB6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE path ADD CONSTRAINT FK_B548B0FEE74C799 FOREIGN KEY (point1_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE path ADD CONSTRAINT FK_B548B0FFCC16877 FOREIGN KEY (point2_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783232B2E93 FOREIGN KEY (address_from_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783293B97A1 FOREIGN KEY (solicitation_linked_id) REFERENCES solicitation (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA967837903D45 FOREIGN KEY (address_to_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783B34B90EE FOREIGN KEY (user_offer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783E5197E49 FOREIGN KEY (user_request_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE waypoint');
        $this->addSql('DROP TABLE ask');
        $this->addSql('DROP TABLE route');
        $this->addSql('ALTER TABLE criteria DROP is_driver, DROP is_passenger');
        $this->addSql('DROP INDEX IDX_DC10F289AF6DAC90 ON matching');
        $this->addSql('DROP INDEX IDX_DC10F2895D3561CC ON matching');
        $this->addSql('DROP INDEX IDX_DC10F2899C131AB7 ON matching');
        $this->addSql('ALTER TABLE matching ADD point_offer_from_id INT DEFAULT NULL, ADD point_offer_to_id INT DEFAULT NULL, ADD point_request_from_id INT DEFAULT NULL, ADD distance_real INT DEFAULT NULL, ADD distance_fly INT DEFAULT NULL, DROP waypoint_offer_origin_id, DROP waypoint_offer_destination_id, DROP waypoint_request_origin_id, DROP distance');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F28985C70859 FOREIGN KEY (point_offer_to_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F28993EF35BA FOREIGN KEY (point_offer_from_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289A54D0C13 FOREIGN KEY (point_request_from_id) REFERENCES point (id)');
        $this->addSql('CREATE INDEX IDX_DC10F28993EF35BA ON matching (point_offer_from_id)');
        $this->addSql('CREATE INDEX IDX_DC10F28985C70859 ON matching (point_offer_to_id)');
        $this->addSql('CREATE INDEX IDX_DC10F289A54D0C13 ON matching (point_request_from_id)');
        $this->addSql('ALTER TABLE proposal ADD journey_type SMALLINT NOT NULL, ADD distance_real INT DEFAULT NULL, ADD distance_fly INT DEFAULT NULL, ADD cape VARCHAR(3) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE distance proposal_linked_journey_id INT DEFAULT NULL, CHANGE type proposal_type SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472558C41CB FOREIGN KEY (proposal_linked_journey_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BFE59472558C41CB ON proposal (proposal_linked_journey_id)');
    }
}
