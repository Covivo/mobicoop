<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240102080906 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE mobconnect__long_distance_subscription SET version = 0, maximum_journeys_number = 3 WHERE created_at BETWEEN \'2023-12-28 13:30\' AND \'2024-01-01 00:00\'');
        $this->addSql('UPDATE mobconnect__long_distance_subscription SET version = 1, maximum_journeys_number = 1 WHERE created_at > \'2024-01-01 00:00\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
