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
final class Version20190320181641 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria ADD min_time TIME DEFAULT NULL, ADD max_time TIME DEFAULT NULL, ADD mon_min_time TIME DEFAULT NULL, ADD mon_max_time TIME DEFAULT NULL, ADD tue_min_time TIME DEFAULT NULL, ADD tue_max_time TIME DEFAULT NULL, ADD wed_min_time TIME DEFAULT NULL, ADD wed_max_time TIME DEFAULT NULL, ADD thu_min_time TIME DEFAULT NULL, ADD thu_max_time TIME DEFAULT NULL, ADD fri_min_time TIME DEFAULT NULL, ADD fri_max_time TIME DEFAULT NULL, ADD sat_min_time TIME DEFAULT NULL, ADD sat_max_time TIME DEFAULT NULL, ADD sun_min_time TIME DEFAULT NULL, ADD sun_max_time TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria DROP min_time, DROP max_time, DROP mon_min_time, DROP mon_max_time, DROP tue_min_time, DROP tue_max_time, DROP wed_min_time, DROP wed_max_time, DROP thu_min_time, DROP thu_max_time, DROP fri_min_time, DROP fri_max_time, DROP sat_min_time, DROP sat_max_time, DROP sun_min_time, DROP sun_max_time');
    }
}
