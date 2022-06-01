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
final class Version20190430134441 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE proposal_community (proposal_id INT NOT NULL, community_id INT NOT NULL, INDEX IDX_2E34B3F5F4792058 (proposal_id), INDEX IDX_2E34B3F5FDA7B0BF (community_id), PRIMARY KEY(proposal_id, community_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE community (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, private TINYINT(1) DEFAULT NULL, description VARCHAR(255) NOT NULL, full_description LONGTEXT NOT NULL, created_date DATETIME NOT NULL, INDEX IDX_1B604033A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE community_proposal (community_id INT NOT NULL, proposal_id INT NOT NULL, INDEX IDX_4DA35155FDA7B0BF (community_id), INDEX IDX_4DA35155F4792058 (proposal_id), PRIMARY KEY(community_id, proposal_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE community_user (id INT AUTO_INCREMENT NOT NULL, community_id INT NOT NULL, user_id INT NOT NULL, admin_id INT DEFAULT NULL, status SMALLINT NOT NULL, created_date DATETIME NOT NULL, accepted_date DATETIME NOT NULL, refused_date DATETIME NOT NULL, INDEX IDX_4CC23C83FDA7B0BF (community_id), INDEX IDX_4CC23C83A76ED395 (user_id), INDEX IDX_4CC23C83642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE proposal_community ADD CONSTRAINT FK_2E34B3F5F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposal_community ADD CONSTRAINT FK_2E34B3F5FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE community ADD CONSTRAINT FK_1B604033A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE community_proposal ADD CONSTRAINT FK_4DA35155FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE community_proposal ADD CONSTRAINT FK_4DA35155F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE image ADD community_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('CREATE INDEX IDX_C53D045FFDA7B0BF ON image (community_id)');
        $this->addSql('ALTER TABLE user ADD created_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proposal_community DROP FOREIGN KEY FK_2E34B3F5FDA7B0BF');
        $this->addSql('ALTER TABLE community_proposal DROP FOREIGN KEY FK_4DA35155FDA7B0BF');
        $this->addSql('ALTER TABLE community_user DROP FOREIGN KEY FK_4CC23C83FDA7B0BF');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FFDA7B0BF');
        $this->addSql('DROP TABLE proposal_community');
        $this->addSql('DROP TABLE community');
        $this->addSql('DROP TABLE community_proposal');
        $this->addSql('DROP TABLE community_user');
        $this->addSql('DROP INDEX IDX_C53D045FFDA7B0BF ON image');
        $this->addSql('ALTER TABLE image DROP community_id');
        $this->addSql('ALTER TABLE user DROP created_date');
    }
}
