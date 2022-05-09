<?php

declare(strict_types=1);

// namespace DoctrineMigrations; // For dev

namespace App\Migrations; // For test/prod

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Evol #4205.
 */
final class Version20220509142454 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
        $this->addSql("UPDATE `badge` SET `text` = 'Bravo vous allez réaliser votre premier covoiturage ! Par votre action, vous limitez les embouteillages, vous améliorez la qualité de l\\'air et en bonus vous faites des économies. Vous êtes un visionnaire, on compte sur vous pour continuer !' WHERE `id` = 3;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
    }
}
