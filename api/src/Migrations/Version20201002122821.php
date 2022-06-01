<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201002122821 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE waypoint DROP INDEX UNIQ_B3DC5881F5B7AF75, ADD INDEX IDX_B3DC5881F5B7AF75 (address_id)');
        $this->addSql('ALTER TABLE direction DROP detail, DROP snapped, DROP geo_json_detail');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE direction ADD detail LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD snapped LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD geo_json_detail LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\'');
        $this->addSql('ALTER TABLE waypoint DROP INDEX IDX_B3DC5881F5B7AF75, ADD UNIQUE INDEX UNIQ_B3DC5881F5B7AF75 (address_id)');
    }
}
