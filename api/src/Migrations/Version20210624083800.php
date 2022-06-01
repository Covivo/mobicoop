<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rename gamification actions
 */
final class Version20210624083800 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_first_ad_posted' WHERE `gamification_action`.`id` = 5;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_ads_posted' WHERE `gamification_action`.`id` = 6;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'community_joined' WHERE `gamification_action`.`id` = 7;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_ad_posted_community' WHERE `gamification_action`.`id` = 8;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_accepted' WHERE `gamification_action`.`id` = 9;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_accepted_community' WHERE `gamification_action`.`id` = 10;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_ad_posted_solidary_exclusive' WHERE `gamification_action`.`id` = 11;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_accepted_n_km' WHERE `gamification_action`.`id` = 12;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_accepted_n_co2_saved' WHERE `gamification_action`.`id` = 13;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'first_answer' WHERE `gamification_action`.`id` = 14;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_ad_renewed' WHERE `gamification_action`.`id` = 15;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_including_relaypoint' WHERE `gamification_action`.`id` = 16;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_ad_posted_event' WHERE `gamification_action`.`id` = 17;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'identify_proof_accepted' WHERE `gamification_action`.`id` = 18;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'electronic_payment_made' WHERE `gamification_action`.`id` = 19;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'user_phone_updated' WHERE `gamification_action`.`id` = 20;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'event_created' WHERE `gamification_action`.`id` = 21;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'community_created' WHERE `gamification_action`.`id` = 22;");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpool_accepted_event' WHERE `gamification_action`.`id` = 23;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
