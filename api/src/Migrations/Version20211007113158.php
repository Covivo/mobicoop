<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210927140754 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `territory_territory` (`id` int(11) NOT NULL, `child_territory_id` int(11) NOT NULL, `parent_territory_id` int(11) NOT NULL) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territory_territory ADD PRIMARY KEY (id), ADD KEY child_territory_id (child_territory_id), ADD KEY parent_territory_id (parent_territory_id)');
        $this->addSql('ALTER TABLE territory_territory  MODIFY id int(11) NOT NULL AUTO_INCREMENT');
        $this->addSql('ALTER TABLE territory ADD `min_latitude` DECIMAL(10,6) NOT NULL AFTER `geo_json_detail`, ADD `max_latitude` DECIMAL(10,6) NOT NULL AFTER `min_latitude`, ADD `min_longitude` DECIMAL(10,6) NOT NULL AFTER `max_latitude`, ADD `max_longitude` DECIMAL(10,6) NOT NULL AFTER `min_longitude`');

        $this->addSql('ALTER TABLE `territory` ADD `admin_level` INT(11) NULL AFTER `geo_json_detail`');
        $this->addSql('ALTER TABLE `territory` ADD SPATIAL `IDX_SPATIAL` (`geo_json_detail`)');
        $this->addSql('ALTER TABLE `territory` ADD UNIQUE `IDX_LATITUDE` (`min_latitude`, `max_latitude`)');
        $this->addSql('ALTER TABLE `territory` ADD UNIQUE `IDX_LONGITUDE` (`min_longitude`, `max_longitude`)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    }
}
