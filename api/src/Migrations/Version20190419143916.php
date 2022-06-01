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
final class Version20190419143916 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE app (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(45) NOT NULL, username VARCHAR(45) NOT NULL, password VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_role (app_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_5247AFCA7987212D (app_id), INDEX IDX_5247AFCAD60322AC (role_id), PRIMARY KEY(app_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territory (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territory_territory (territory_source INT NOT NULL, territory_target INT NOT NULL, INDEX IDX_44A1E66BCA8FFFF7 (territory_source), INDEX IDX_44A1E66BD36AAF78 (territory_target), PRIMARY KEY(territory_source, territory_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(45) NOT NULL, name VARCHAR(45) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_right (role_id INT NOT NULL, right_id INT NOT NULL, INDEX IDX_43169D3BD60322AC (role_id), INDEX IDX_43169D3B54976835 (right_id), PRIMARY KEY(role_id, right_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE uright (id INT AUTO_INCREMENT NOT NULL, type SMALLINT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE right_right (right_source INT NOT NULL, right_target INT NOT NULL, INDEX IDX_4C20EE145F7295E8 (right_source), INDEX IDX_4C20EE144697C567 (right_target), PRIMARY KEY(right_source, right_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_right (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, right_id INT NOT NULL, INDEX IDX_56088E4CA76ED395 (user_id), INDEX IDX_56088E4C54976835 (right_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_right_territory (user_right_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_1D1963B0B41A8C35 (user_right_id), INDEX IDX_1D1963B073F74AD4 (territory_id), PRIMARY KEY(user_right_id, territory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role_territory (user_role_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_D30535E38E0E3CA6 (user_role_id), INDEX IDX_D30535E373F74AD4 (territory_id), PRIMARY KEY(user_role_id, territory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_role ADD CONSTRAINT FK_5247AFCA7987212D FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_role ADD CONSTRAINT FK_5247AFCAD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territory_territory ADD CONSTRAINT FK_44A1E66BCA8FFFF7 FOREIGN KEY (territory_source) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territory_territory ADD CONSTRAINT FK_44A1E66BD36AAF78 FOREIGN KEY (territory_target) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_right ADD CONSTRAINT FK_43169D3BD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_right ADD CONSTRAINT FK_43169D3B54976835 FOREIGN KEY (right_id) REFERENCES uright (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE right_right ADD CONSTRAINT FK_4C20EE145F7295E8 FOREIGN KEY (right_source) REFERENCES uright (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE right_right ADD CONSTRAINT FK_4C20EE144697C567 FOREIGN KEY (right_target) REFERENCES uright (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4C54976835 FOREIGN KEY (right_id) REFERENCES uright (id)');
        $this->addSql('ALTER TABLE user_right_territory ADD CONSTRAINT FK_1D1963B0B41A8C35 FOREIGN KEY (user_right_id) REFERENCES user_right (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_right_territory ADD CONSTRAINT FK_1D1963B073F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE user_role_territory ADD CONSTRAINT FK_D30535E38E0E3CA6 FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role_territory ADD CONSTRAINT FK_D30535E373F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        // insert FO app user and default roles
        $this->addSql('INSERT INTO `app` (`id`, `name`, `username`, `password`) VALUES (1, \'FO\', \'front\', \'$2y$13$ZmXMawAMfTITXntmHimJBOnE3ukVNi1NT6t6141SgaXvlm0Q8Ga52\');');
        $this->addSql('INSERT INTO `role` (`id`, `title`, `name`) VALUES (1, \'User\', \'ROLE_USER\'),(2, \'Admin\', \'ROLE_ADMIN\');');
        $this->addSql('INSERT INTO `app_role` (`app_id`, `role_id`) VALUES (1, 1);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_role DROP FOREIGN KEY FK_5247AFCA7987212D');
        $this->addSql('ALTER TABLE territory_territory DROP FOREIGN KEY FK_44A1E66BCA8FFFF7');
        $this->addSql('ALTER TABLE territory_territory DROP FOREIGN KEY FK_44A1E66BD36AAF78');
        $this->addSql('ALTER TABLE user_right_territory DROP FOREIGN KEY FK_1D1963B073F74AD4');
        $this->addSql('ALTER TABLE user_role_territory DROP FOREIGN KEY FK_D30535E373F74AD4');
        $this->addSql('ALTER TABLE app_role DROP FOREIGN KEY FK_5247AFCAD60322AC');
        $this->addSql('ALTER TABLE role_right DROP FOREIGN KEY FK_43169D3BD60322AC');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE role_right DROP FOREIGN KEY FK_43169D3B54976835');
        $this->addSql('ALTER TABLE right_right DROP FOREIGN KEY FK_4C20EE145F7295E8');
        $this->addSql('ALTER TABLE right_right DROP FOREIGN KEY FK_4C20EE144697C567');
        $this->addSql('ALTER TABLE user_right DROP FOREIGN KEY FK_56088E4C54976835');
        $this->addSql('ALTER TABLE user_right_territory DROP FOREIGN KEY FK_1D1963B0B41A8C35');
        $this->addSql('ALTER TABLE user_role_territory DROP FOREIGN KEY FK_D30535E38E0E3CA6');
        $this->addSql('DROP TABLE app');
        $this->addSql('DROP TABLE app_role');
        $this->addSql('DROP TABLE territory');
        $this->addSql('DROP TABLE territory_territory');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_right');
        $this->addSql('DROP TABLE uright');
        $this->addSql('DROP TABLE right_right');
        $this->addSql('DROP TABLE user_right');
        $this->addSql('DROP TABLE user_right_territory');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE user_role_territory');
    }
}
