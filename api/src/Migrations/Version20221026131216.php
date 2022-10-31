<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221026131216 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Citiz icon
        $this->addSql("INSERT INTO `icon` (`id`, `private_icon_linked_id`, `name`, `file_name`) VALUES (26, NULL,'relaypoint-citiz', 'relaypoint-citiz.svg')");
        // Citiz station type
        $this->addSql("INSERT INTO `relay_point_type` (`id`, `name`, `created_date`, `updated_date`, `icon_id`) VALUES(13, 'Station-Citiz', NOW(), NULL, 26);");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `icon` WHERE `icon`.`id` = 26;');
        $this->addSql('DELETE FROM `relay_point_type` WHERE `relay_point_type`.`id` = 13;');
    }
}
