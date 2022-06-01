<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190531140115 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE address ADD geo_json POINT DEFAULT NULL COMMENT \'(DC2Type:point)\'');
        $this->addSql('ALTER TABLE direction ADD geo_json_bbox POLYGON DEFAULT NULL COMMENT \'(DC2Type:polygon)\', ADD geo_json_detail LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\'');
        $this->addSql('ALTER TABLE territory CHANGE detail geo_json_detail MULTIPOLYGON NOT NULL COMMENT \'(DC2Type:multipolygon)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE address DROP geo_json');
        $this->addSql('ALTER TABLE direction DROP geo_json_bbox, DROP geo_json_detail');
        $this->addSql('ALTER TABLE territory CHANGE geo_json_detail detail MULTIPOLYGON NOT NULL COMMENT \'(DC2Type:multipolygon)\'');
    }
}
