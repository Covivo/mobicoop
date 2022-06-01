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
final class Version20190507131703 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE right_right');
        $this->addSql('DROP TABLE user_right_territory');
        $this->addSql('DROP TABLE user_role_territory');
        $this->addSql('ALTER TABLE direction CHANGE bearing bearing INT DEFAULT NULL');
        $this->addSql('ALTER TABLE uright ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE uright ADD CONSTRAINT FK_98C5BFD8727ACA70 FOREIGN KEY (parent_id) REFERENCES uright (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98C5BFD8727ACA70 ON uright (parent_id)');
        $this->addSql('ALTER TABLE role ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A727ACA70 FOREIGN KEY (parent_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57698A6A727ACA70 ON role (parent_id)');
        $this->addSql('ALTER TABLE user_right ADD territory_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4C73F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('CREATE INDEX IDX_56088E4C73F74AD4 ON user_right (territory_id)');
        $this->addSql('ALTER TABLE user_role ADD territory_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A373F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('CREATE INDEX IDX_2DE8C6A373F74AD4 ON user_role (territory_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE right_right (right_source INT NOT NULL, right_target INT NOT NULL, INDEX IDX_4C20EE145F7295E8 (right_source), INDEX IDX_4C20EE144697C567 (right_target), PRIMARY KEY(right_source, right_target)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_right_territory (user_right_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_1D1963B0B41A8C35 (user_right_id), INDEX IDX_1D1963B073F74AD4 (territory_id), PRIMARY KEY(user_right_id, territory_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_role_territory (user_role_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_D30535E38E0E3CA6 (user_role_id), INDEX IDX_D30535E373F74AD4 (territory_id), PRIMARY KEY(user_role_id, territory_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE right_right ADD CONSTRAINT FK_4C20EE144697C567 FOREIGN KEY (right_target) REFERENCES uright (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE right_right ADD CONSTRAINT FK_4C20EE145F7295E8 FOREIGN KEY (right_source) REFERENCES uright (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_right_territory ADD CONSTRAINT FK_1D1963B073F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_right_territory ADD CONSTRAINT FK_1D1963B0B41A8C35 FOREIGN KEY (user_right_id) REFERENCES user_right (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role_territory ADD CONSTRAINT FK_D30535E373F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role_territory ADD CONSTRAINT FK_D30535E38E0E3CA6 FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE direction CHANGE bearing bearing INT NOT NULL');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6A727ACA70');
        $this->addSql('DROP INDEX UNIQ_57698A6A727ACA70 ON role');
        $this->addSql('ALTER TABLE role DROP parent_id');
        $this->addSql('ALTER TABLE uright DROP FOREIGN KEY FK_98C5BFD8727ACA70');
        $this->addSql('DROP INDEX UNIQ_98C5BFD8727ACA70 ON uright');
        $this->addSql('ALTER TABLE uright DROP parent_id');
        $this->addSql('ALTER TABLE user_right DROP FOREIGN KEY FK_56088E4C73F74AD4');
        $this->addSql('DROP INDEX IDX_56088E4C73F74AD4 ON user_right');
        $this->addSql('ALTER TABLE user_right DROP territory_id');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A373F74AD4');
        $this->addSql('DROP INDEX IDX_2DE8C6A373F74AD4 ON user_role');
        $this->addSql('ALTER TABLE user_role DROP territory_id');
    }
}
