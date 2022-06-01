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
final class Version20181001130100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proposal ADD proposal_linked_id INT DEFAULT NULL, ADD proposal_linked_journey_id INT DEFAULT NULL, ADD proposal_origin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947263826222 FOREIGN KEY (proposal_linked_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472558C41CB FOREIGN KEY (proposal_linked_journey_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947263F6800A FOREIGN KEY (proposal_origin_id) REFERENCES proposal (id)');
        $this->addSql('CREATE INDEX IDX_BFE5947263826222 ON proposal (proposal_linked_id)');
        $this->addSql('CREATE INDEX IDX_BFE59472558C41CB ON proposal (proposal_linked_journey_id)');
        $this->addSql('CREATE INDEX IDX_BFE5947263F6800A ON proposal (proposal_origin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947263826222');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472558C41CB');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947263F6800A');
        $this->addSql('DROP INDEX IDX_BFE5947263826222 ON proposal');
        $this->addSql('DROP INDEX IDX_BFE59472558C41CB ON proposal');
        $this->addSql('DROP INDEX IDX_BFE5947263F6800A ON proposal');
        $this->addSql('ALTER TABLE proposal DROP proposal_linked_id, DROP proposal_linked_journey_id, DROP proposal_origin_id');
    }
}
