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
final class Version20190301140736 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria ADD proposal_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B61F9B81F4792058 ON criteria (proposal_id)');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472990BEA15');
        $this->addSql('DROP INDEX UNIQ_BFE59472990BEA15 ON proposal');
        $this->addSql('ALTER TABLE proposal DROP criteria_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81F4792058');
        $this->addSql('DROP INDEX UNIQ_B61F9B81F4792058 ON criteria');
        $this->addSql('ALTER TABLE criteria DROP proposal_id');
        $this->addSql('ALTER TABLE proposal ADD criteria_id INT NOT NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BFE59472990BEA15 ON proposal (criteria_id)');
    }
}
