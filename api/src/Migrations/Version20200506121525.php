<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200506121525 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_matching ADD original_distance INT DEFAULT NULL, ADD accepted_detour_distance INT DEFAULT NULL, ADD detour_distance INT DEFAULT NULL, ADD detour_distance_percent NUMERIC(5, 2) DEFAULT NULL, ADD original_duration INT DEFAULT NULL, ADD accepted_detour_duration INT DEFAULT NULL, ADD detour_duration INT DEFAULT NULL, ADD detour_duration_percent NUMERIC(5, 2) DEFAULT NULL, ADD common_distance INT DEFAULT NULL, ADD pick_up_duration INT DEFAULT NULL, ADD drop_off_duration INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_matching DROP original_distance, DROP accepted_detour_distance, DROP detour_distance, DROP detour_distance_percent, DROP original_duration, DROP accepted_detour_duration, DROP detour_duration, DROP detour_duration_percent, DROP common_distance, DROP pick_up_duration, DROP drop_off_duration');
    }
}
