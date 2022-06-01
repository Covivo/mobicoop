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
final class Version20190318112639 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE direction_zone DROP FOREIGN KEY FK_8890F4F9F2C3FAB');
        $this->addSql('ALTER TABLE near DROP FOREIGN KEY FK_764C1C118542D422');
        $this->addSql('ALTER TABLE near DROP FOREIGN KEY FK_764C1C1197F77BCC');
        $this->addSql('DROP TABLE direction_zone');
        $this->addSql('DROP TABLE near');
        $this->addSql('DROP TABLE zone');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE direction_zone (direction_id INT NOT NULL, zone_id INT NOT NULL, INDEX IDX_8890F4FAF73D997 (direction_id), INDEX IDX_8890F4F9F2C3FAB (zone_id), PRIMARY KEY(direction_id, zone_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE near (id INT AUTO_INCREMENT NOT NULL, zone1_id INT NOT NULL, zone2_id INT NOT NULL, INDEX IDX_764C1C118542D422 (zone2_id), INDEX IDX_764C1C1197F77BCC (zone1_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, from_lat NUMERIC(10, 6) NOT NULL, to_lat NUMERIC(10, 6) NOT NULL, from_lon NUMERIC(10, 6) NOT NULL, to_lon NUMERIC(10, 6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE direction_zone ADD CONSTRAINT FK_8890F4F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE direction_zone ADD CONSTRAINT FK_8890F4FAF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE near ADD CONSTRAINT FK_764C1C118542D422 FOREIGN KEY (zone2_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE near ADD CONSTRAINT FK_764C1C1197F77BCC FOREIGN KEY (zone1_id) REFERENCES zone (id)');
    }
}
