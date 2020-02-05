<?php declare(strict_types=1);

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
final class Version20200115154255 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(
            'INSERT INTO `uright` (`id`, `type`, `name`, `parent_id`, `description`) VALUES 
        (86, 1, \'user_read\', 1, \'Read a user\'), 
        (87, 1, \'user_read_self\', 4, \'Read itself\'), 
        (88, 1, \'user_messages\', 1, \'Access to the messages of a user\'), 
        (89, 1, \'user_messages_self\', 4, \'User access to its own messages\'), 
        (90, 1, \'user_asks\', 1, \'Access to the asks of a user\'), 
        (91, 1, \'user_asks_self\', 4, \'User access to its own asks\'),
        (92, 1, \'event_read\', NULL, \'View an event\'),
        (93, 2, \'action_manage\', NULL, \'Manage actions\'),
        (94, 1, \'action_read\', 93, \'Read an action\'),
        (95, 2, \'log_manage\', NULL, \'Manage log records\'),
        (96, 1, \'log_read\', 95, \'Read a log record\')'
        );
        $this->addSql(
            'INSERT INTO `role_right` (`role_id`, `right_id`) VALUES
        (5, 92),
        (2, 93),
        (2, 95)'
        );
        $this->addSql(
            'UPDATE `role` SET parent_id=1 WHERE id=2'
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `role_right` WHERE `role_id` = 5 AND `right_id` = 92;');
        $this->addSql('DELETE FROM `role_right` WHERE `role_id` = 2 AND `right_id` = 93;');
        $this->addSql('DELETE FROM `role_right` WHERE `role_id` = 2 AND `right_id` = 95;');
        $this->addSql('DELETE FROM `uright` WHERE `id` IN (86,87,88,89,90,91,92,93,94,95,96);');
    }
}
