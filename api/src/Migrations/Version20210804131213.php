<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210804131213 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE badge CHANGE text text VARCHAR(512) NOT NULL');

        $this->addSql("INSERT INTO `badge` (`id`, `name`, `title`, `text`, `status`, `public`, `start_date`, `end_date`, `created_date`, `updated_date`) VALUES (1, 'remove_the_mask', 'Je lève le masque', 'Bravo! Avec votre profil complètement renseigné, les autres usagers seront plus incités à vous contacter et vous contribuez à une image de confiance et transparence sur la plateforme.', '1', '1', NULL, NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `badge` (`id`, `name`, `title`, `text`, `status`, `public`, `start_date`, `end_date`, `created_date`, `updated_date`) VALUES(2, 'launch', 'Je me lance', 'Publier une annonce est l\'acte fondateur du covoiturage, félicitations !\r\nSi vous avez publié une annonce Conducteur, avez-vous envisagé à la passer en \"Peu importe\" afin de pouvoir le cas échéant être passager et augmenter vos chances de trouver un covoitureur', '1', '1', NULL, NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `badge` (`id`, `name`, `title`, `text`, `status`, `public`, `start_date`, `end_date`, `created_date`, `updated_date`) VALUES(3, 'first_time', 'Ma toute première fois', 'C\'est émouvant non ? Vous allez pouvoir grâce à votre plateforme, remplir davantage une voiture, limiter les embouteillages, améliorer la qualité de l\'air, garder des sous pour autre chose qu\'un déplacement, et vivre autre chose de plus solidaire qu\'un simple déplacement fonctionnel.', '1', '1', NULL, NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `badge` (`id`, `name`, `title`, `text`, `status`, `public`, `start_date`, `end_date`, `created_date`, `updated_date`) VALUES(4, 'welcome', 'Bienvenue dans l\'équipe', 'Votre intégration dans votre communauté est complète, chapeau ! À vous maintenant de convaincre les autres membres de la communauté...', '1', '1', NULL, NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `badge` (`id`, `name`, `title`, `text`, `status`, `public`, `start_date`, `end_date`, `created_date`, `updated_date`) VALUES (5, 'rally', 'Rallions-nous', 'Vous êtes maintenant un expert patenté du covoiturage! Vous en maîtrisez toutes les astuces. Pensez bien à les partager avec vos covoitureurs!', '1', '1', NULL, NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `badge` (`id`, `name`, `title`, `text`, `status`, `public`, `start_date`, `end_date`, `created_date`, `updated_date`) VALUES (6, 'km_carpooled', 'Engagez-vous, rengagez-vous!', 'Votre constance et votre fidélité forcent le respect! Chapeau bas!', '1', '1', NULL, NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `badge` (`id`, `name`, `title`, `text`, `status`, `public`, `start_date`, `end_date`, `created_date`, `updated_date`) VALUES (7, 'carbon_saved', 'Je pèse mon quintal comme écolo !', '100kg de CO² économisé, ça commence à peser, fichtre !\r\nÀ raison de l\'ordre de 0,1kg par kilomètre, les forts en math estimeront la distance nécessaire...\r\nPour vous donner un order de grandeur, saviez-vous que la production et la fin de vie d\'une voiture thermique a un bilan carbone moyen de 6,7t (et 10,2t pour une électrique)* ?\r\n\r\n*Source: Carbone4', '1', '1', NULL, NULL, NULL, NULL);");

        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '1', '1', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '1', '2', '2', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '1', '3', '3', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '1', '4', '4', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '2', '5', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '3', '14', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '3', '9', '2', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '4', '22', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '4', '8', '2', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '4', '10', '3', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '5', '6', '1', '5', NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '6', '12', '1', '500', NULL, NULL);");
        $this->addSql("INSERT INTO `sequence_item` (`id`, `badge_id`, `gamification_action_id`, `position`, `min_count`, `min_unique_count`, `in_date_range`) VALUES (NULL, '7', '13', '1', '100', NULL, NULL);");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE badge CHANGE text text VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
