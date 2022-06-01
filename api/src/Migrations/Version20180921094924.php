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
final class Version20180921094924 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, street_address VARCHAR(255) NOT NULL, postal_code VARCHAR(15) DEFAULT NULL, address_locality VARCHAR(100) NOT NULL, address_country VARCHAR(100) NOT NULL, latitude NUMERIC(10, 6) DEFAULT NULL, longitude NUMERIC(10, 6) DEFAULT NULL, elevation INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, given_name VARCHAR(100) DEFAULT NULL, family_name VARCHAR(100) DEFAULT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(100) DEFAULT NULL, gender VARCHAR(30) DEFAULT NULL, nationality VARCHAR(100) DEFAULT NULL, birth_date DATE DEFAULT NULL, telephone VARCHAR(100) DEFAULT NULL, max_deviation_time INT DEFAULT NULL, max_deviation_distance INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, address_id INT NOT NULL, name VARCHAR(45) NOT NULL, INDEX IDX_5543718BA76ED395 (user_id), INDEX IDX_5543718BF5B7AF75 (address_id), UNIQUE INDEX UNIQ_5543718B5E237E06A76ED395 (name, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718BF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_address DROP FOREIGN KEY FK_5543718BF5B7AF75');
        $this->addSql('ALTER TABLE user_address DROP FOREIGN KEY FK_5543718BA76ED395');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_address');
    }
}
