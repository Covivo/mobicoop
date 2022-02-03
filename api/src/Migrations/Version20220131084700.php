<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220131084700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 15 WHERE `relay_point_type`.`id` = 1');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 19 WHERE `relay_point_type`.`id` = 2');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 22 WHERE `relay_point_type`.`id` = 3');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 20 WHERE `relay_point_type`.`id` = 4');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 1 WHERE `relay_point_type`.`id` = 5');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 1 WHERE `relay_point_type`.`id` = 6');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 14 WHERE `relay_point_type`.`id` = 7');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 23 WHERE `relay_point_type`.`id` = 8');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 12 WHERE `relay_point_type`.`id` = 9');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 3 WHERE `relay_point_type`.`id` = 10');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 17 WHERE `relay_point_type`.`id` = 11');
        $this->addSql('UPDATE `relay_point_type` SET `icon_id` = 24 WHERE `relay_point_type`.`id` = 12');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
