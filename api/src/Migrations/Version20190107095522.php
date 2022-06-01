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
final class Version20190107095522 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ptline ADD direction VARCHAR(255) DEFAULT NULL, CHANGE origin origin VARCHAR(100) DEFAULT NULL, CHANGE destination destination VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE ptleg CHANGE ptline_id ptline_id INT DEFAULT NULL, CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ptstep CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ptjourney CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ptjourney CHANGE distance distance INT NOT NULL, CHANGE duration duration INT NOT NULL');
        $this->addSql('ALTER TABLE ptleg CHANGE ptline_id ptline_id INT NOT NULL, CHANGE distance distance INT NOT NULL, CHANGE duration duration INT NOT NULL');
        $this->addSql('ALTER TABLE ptline DROP direction, CHANGE origin origin VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE destination destination VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ptstep CHANGE distance distance INT NOT NULL, CHANGE duration duration INT NOT NULL');
    }
}
