<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210819133003 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE `gamification_action` SET `title` = 'Adresse email validée' WHERE `gamification_action`.`id` = 1");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Numéro de téléphone validé' WHERE `gamification_action`.`id` = 2;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Photo de profil renseignée' WHERE `gamification_action`.`id` = 3;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Commune de résidence renseignée' WHERE `gamification_action`.`id` = 4;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Première annonce publiée' WHERE `gamification_action`.`id` = 5;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'N annonces publiées' WHERE `gamification_action`.`id` = 6;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Une communauté rejointe' WHERE `gamification_action`.`id` = 7;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Une annonce publiée dans une communauté' WHERE `gamification_action`.`id` = 8;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Covoiturage accepté' WHERE `gamification_action`.`id` = 9;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Covoiturage accepté lié à une communauté' WHERE `gamification_action`.`id` = 10;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Annonce solidaire exclusive publiée' WHERE `gamification_action`.`id` = 11;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'N km de covoiturages acceptés' WHERE `gamification_action`.`id` = 12;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'N kg de CO² économisés' WHERE `gamification_action`.`id` = 13;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Premier message répondu' WHERE `gamification_action`.`id` = 14;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Annonce périmée relancée' WHERE `gamification_action`.`id` = 15;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Annonce avec un point-relais publiée' WHERE `gamification_action`.`id` = 16;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Une annonce publiée liée à un événement' WHERE `gamification_action`.`id` = 17;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Identité bancaire renseignée' WHERE `gamification_action`.`id` = 18;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Premier paiement électronique réalisé' WHERE `gamification_action`.`id` = 19;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Numéro de téléphone renseigné' WHERE `gamification_action`.`id` = 20;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Événement publié' WHERE `gamification_action`.`id` = 21;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Communauté créée' WHERE `gamification_action`.`id` = 22;");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Covoiturage accepté lié à un événement' WHERE `gamification_action`.`id` = 23;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE relay_point DROP external_id, DROP external_author, DROP external_updated_date');
    }
}
