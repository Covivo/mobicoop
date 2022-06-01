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
final class Version20181001122051 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE criteria (id INT AUTO_INCREMENT NOT NULL, frequency SMALLINT NOT NULL, seats INT NOT NULL, from_date DATE NOT NULL, from_time TIME DEFAULT NULL, to_date DATE DEFAULT NULL, mon_check TINYINT(1) DEFAULT NULL, tue_check TINYINT(1) DEFAULT NULL, wed_check TINYINT(1) DEFAULT NULL, thu_check TINYINT(1) DEFAULT NULL, fri_check TINYINT(1) DEFAULT NULL, sat_check TINYINT(1) DEFAULT NULL, sun_check TINYINT(1) DEFAULT NULL, mon_time TIME DEFAULT NULL, tue_time TIME DEFAULT NULL, wed_time TIME DEFAULT NULL, thu_time TIME DEFAULT NULL, fri_time TIME DEFAULT NULL, sat_time TIME DEFAULT NULL, sun_time TIME DEFAULT NULL, margin_time INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE matching (id INT AUTO_INCREMENT NOT NULL, proposal_offer_id INT NOT NULL, proposal_request_id INT NOT NULL, point_offer_from_id INT DEFAULT NULL, point_offer_to_id INT DEFAULT NULL, point_request_from_id INT DEFAULT NULL, criteria_id INT NOT NULL, created_date DATETIME NOT NULL, distance_real INT DEFAULT NULL, distance_fly INT DEFAULT NULL, duration INT DEFAULT NULL, INDEX IDX_DC10F289B29D48C6 (proposal_offer_id), INDEX IDX_DC10F289304C8BD3 (proposal_request_id), INDEX IDX_DC10F28993EF35BA (point_offer_from_id), INDEX IDX_DC10F28985C70859 (point_offer_to_id), INDEX IDX_DC10F289A54D0C13 (point_request_from_id), UNIQUE INDEX UNIQ_DC10F289990BEA15 (criteria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE path (id INT AUTO_INCREMENT NOT NULL, point1_id INT NOT NULL, point2_id INT NOT NULL, travel_mode_id INT DEFAULT NULL, position INT NOT NULL, detail LONGTEXT NOT NULL, encode_format SMALLINT NOT NULL, INDEX IDX_B548B0FEE74C799 (point1_id), INDEX IDX_B548B0FFCC16877 (point2_id), INDEX IDX_B548B0FB6A6325B (travel_mode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE point (id INT AUTO_INCREMENT NOT NULL, proposal_id INT NOT NULL, address_id INT NOT NULL, travel_mode_id INT DEFAULT NULL, position SMALLINT NOT NULL, last_point TINYINT(1) NOT NULL, distance_next_real INT DEFAULT NULL, distance_next_fly INT DEFAULT NULL, duration_next INT DEFAULT NULL, INDEX IDX_B7A5F324F4792058 (proposal_id), INDEX IDX_B7A5F324F5B7AF75 (address_id), INDEX IDX_B7A5F324B6A6325B (travel_mode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposal (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, criteria_id INT NOT NULL, proposal_type SMALLINT NOT NULL, journey_type SMALLINT NOT NULL, created_date DATETIME NOT NULL, distance_real INT DEFAULT NULL, distance_fly INT DEFAULT NULL, duration INT DEFAULT NULL, cape VARCHAR(3) DEFAULT NULL, INDEX IDX_BFE59472A76ED395 (user_id), UNIQUE INDEX UNIQ_BFE59472990BEA15 (criteria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposal_travel_mode (proposal_id INT NOT NULL, travel_mode_id INT NOT NULL, INDEX IDX_E586423CF4792058 (proposal_id), INDEX IDX_E586423CB6A6325B (travel_mode_id), PRIMARY KEY(proposal_id, travel_mode_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solicitation (id INT AUTO_INCREMENT NOT NULL, address_from_id INT NOT NULL, address_to_id INT NOT NULL, user_id INT NOT NULL, user_offer_id INT NOT NULL, user_request_id INT NOT NULL, matching_id INT NOT NULL, criteria_id INT NOT NULL, status SMALLINT NOT NULL, journey_type SMALLINT NOT NULL, created_date DATETIME NOT NULL, distance_real INT DEFAULT NULL, distance_fly INT DEFAULT NULL, duration INT DEFAULT NULL, INDEX IDX_4FA96783232B2E93 (address_from_id), INDEX IDX_4FA967837903D45 (address_to_id), INDEX IDX_4FA96783A76ED395 (user_id), INDEX IDX_4FA96783B34B90EE (user_offer_id), INDEX IDX_4FA96783E5197E49 (user_request_id), INDEX IDX_4FA96783B39876B8 (matching_id), UNIQUE INDEX UNIQ_4FA96783990BEA15 (criteria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE travel_mode (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289B29D48C6 FOREIGN KEY (proposal_offer_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289304C8BD3 FOREIGN KEY (proposal_request_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F28993EF35BA FOREIGN KEY (point_offer_from_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F28985C70859 FOREIGN KEY (point_offer_to_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289A54D0C13 FOREIGN KEY (point_request_from_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE path ADD CONSTRAINT FK_B548B0FEE74C799 FOREIGN KEY (point1_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE path ADD CONSTRAINT FK_B548B0FFCC16877 FOREIGN KEY (point2_id) REFERENCES point (id)');
        $this->addSql('ALTER TABLE path ADD CONSTRAINT FK_B548B0FB6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE proposal_travel_mode ADD CONSTRAINT FK_E586423CF4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposal_travel_mode ADD CONSTRAINT FK_E586423CB6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783232B2E93 FOREIGN KEY (address_from_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA967837903D45 FOREIGN KEY (address_to_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783B34B90EE FOREIGN KEY (user_offer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783E5197E49 FOREIGN KEY (user_request_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289990BEA15');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472990BEA15');
        $this->addSql('ALTER TABLE solicitation DROP FOREIGN KEY FK_4FA96783990BEA15');
        $this->addSql('ALTER TABLE solicitation DROP FOREIGN KEY FK_4FA96783B39876B8');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F28993EF35BA');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F28985C70859');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289A54D0C13');
        $this->addSql('ALTER TABLE path DROP FOREIGN KEY FK_B548B0FEE74C799');
        $this->addSql('ALTER TABLE path DROP FOREIGN KEY FK_B548B0FFCC16877');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289B29D48C6');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289304C8BD3');
        $this->addSql('ALTER TABLE point DROP FOREIGN KEY FK_B7A5F324F4792058');
        $this->addSql('ALTER TABLE proposal_travel_mode DROP FOREIGN KEY FK_E586423CF4792058');
        $this->addSql('ALTER TABLE path DROP FOREIGN KEY FK_B548B0FB6A6325B');
        $this->addSql('ALTER TABLE point DROP FOREIGN KEY FK_B7A5F324B6A6325B');
        $this->addSql('ALTER TABLE proposal_travel_mode DROP FOREIGN KEY FK_E586423CB6A6325B');
        $this->addSql('DROP TABLE criteria');
        $this->addSql('DROP TABLE matching');
        $this->addSql('DROP TABLE path');
        $this->addSql('DROP TABLE point');
        $this->addSql('DROP TABLE proposal');
        $this->addSql('DROP TABLE proposal_travel_mode');
        $this->addSql('DROP TABLE solicitation');
        $this->addSql('DROP TABLE travel_mode');
    }
}
