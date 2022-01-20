<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111092900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO `relay_point_type` (`id`, `name`, `created_date`, `updated_date`, `icon_id`) VALUES
        (1, 'Aire-covoiturage', NOW(), NULL, 15),
        (2, 'P+R', NOW(), NULL, 19),
        (3, 'Gare', NOW(), NULL, 22),
        (4, 'Parking', NOW(), NULL, 20),
        (5, 'Zone activité', NOW(), NULL, 1),
        (6, 'aucun', NOW(), NULL, 1),
        (7, 'Arrêt', NOW(), NULL, 14),
        (8, 'Bâtiment', NOW(), NULL, 23),
        (9, 'Taxi', NOW(), NULL, 12),
        (10, 'Communaute', NOW(), NULL, 3),
        (11, 'Arrêt-covoiturage', NOW(), NULL, 17),
        (12, 'Arrêt Rezo Pouce', NOW(), NULL, 25);
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
