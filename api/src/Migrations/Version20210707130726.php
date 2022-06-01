<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210707130726 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE gamification_action CHANGE name title VARCHAR(255) NOT NULL');

        $this->addSql("UPDATE `gamification_action` SET `title` = 'Valider son adresse email' WHERE `gamification_action`.`id` = 1");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Valider son numéro de téléphone' WHERE `gamification_action`.`id` = 2");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Renseigner sa photo de profil' WHERE `gamification_action`.`id` = 3");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Renseigner sa commune de résidence' WHERE `gamification_action`.`id` = 4");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Publier sa première annonce' WHERE `gamification_action`.`id` = 5");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Publier N annonces' WHERE `gamification_action`.`id` = 6");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Rejoindre une communauté' WHERE `gamification_action`.`id` = 7");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Publier une annonce dans une communauté' WHERE `gamification_action`.`id` = 8");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Accepter un covoiturage' WHERE `gamification_action`.`id` = 9");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Avoir un covoiturage accepté lié à une communauté' WHERE `gamification_action`.`id` = 10");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Publier une annonce solidaire exclusive' WHERE `gamification_action`.`id` = 11");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Avoir accepté N kilomètres de covoiturage' WHERE `gamification_action`.`id` = 12");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Avoir accepté N CO2 économisé de covoiturage' WHERE `gamification_action`.`id` = 13");
        $this->addSql("UPDATE `gamification_action` SET `title` = '1ère réponse à un message' WHERE `gamification_action`.`id` = 14");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Relancer une annonce régulière périmée' WHERE `gamification_action`.`id` = 15");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Utiliser un point-relais comme lieu ou étape dans une annonce' WHERE `gamification_action`.`id` = 16");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Publier une annonce dans un événement' WHERE `gamification_action`.`id` = 17");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Renseigner son identité bancairel' WHERE `gamification_action`.`id` = 18");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Réaliser son premier paiement électronique' WHERE `gamification_action`.`id` = 19");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Renseigner son numéro de téléphone' WHERE `gamification_action`.`id` = 20");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Publier un événement' WHERE `gamification_action`.`id` = 21");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Créer une communauté' WHERE `gamification_action`.`id` = 22");
        $this->addSql("UPDATE `gamification_action` SET `title` = 'Avoir un covoiturage accepté lié à un événement' WHERE `gamification_action`.`id` = 23");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE gamification_action CHANGE title name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
