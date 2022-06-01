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
final class Version20190214171033 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria ADD tue_margin_time INT DEFAULT NULL, ADD wed_margin_time INT DEFAULT NULL, ADD thu_margin_time INT DEFAULT NULL, ADD fri_margin_time INT DEFAULT NULL, ADD sat_margin_time INT DEFAULT NULL, ADD sun_margin_time INT DEFAULT NULL, ADD price_km NUMERIC(4, 2) DEFAULT NULL, CHANGE margin_time mon_margin_time INT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal ADD comment LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE car ADD price_km NUMERIC(4, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE car DROP price_km');
        $this->addSql('ALTER TABLE criteria ADD margin_time INT DEFAULT NULL, DROP mon_margin_time, DROP tue_margin_time, DROP wed_margin_time, DROP thu_margin_time, DROP fri_margin_time, DROP sat_margin_time, DROP sun_margin_time, DROP price_km');
        $this->addSql('ALTER TABLE proposal DROP comment');
    }
}
