<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200327073919 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE78E28472');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE83850A9D');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEB524543A');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEB93F8B63');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEE3374B2');
        $this->addSql('DROP INDEX UNIQ_59B969CE83850A9D ON carpool_proof');
        $this->addSql('DROP INDEX IDX_59B969CEB93F8B63 ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEE3374B2 ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEB524543A ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CE78E28472 ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof ADD pick_up_passenger_address_id INT DEFAULT NULL, ADD pick_up_driver_address_id INT DEFAULT NULL, ADD drop_off_passenger_address_id INT DEFAULT NULL, ADD drop_off_driver_address_id INT DEFAULT NULL, ADD driver_id INT DEFAULT NULL, ADD passenger_id INT DEFAULT NULL, ADD status SMALLINT NOT NULL, ADD pick_up_passenger_date DATETIME DEFAULT NULL, ADD pick_up_driver_date DATETIME DEFAULT NULL, ADD drop_off_passenger_date DATETIME DEFAULT NULL, ADD drop_off_driver_date DATETIME DEFAULT NULL, ADD geo_json_points LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\', DROP ask_id, DROP pickup_passenger_address_id, DROP pickup_driver_address_id, DROP dropoff_passenger_address_id, DROP dropoff_driver_address_id');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEA20FE83A FOREIGN KEY (pick_up_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CED7021172 FOREIGN KEY (pick_up_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE78B067B FOREIGN KEY (drop_off_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEC76281BF FOREIGN KEY (drop_off_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEC3423909 FOREIGN KEY (driver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE4502E565 FOREIGN KEY (passenger_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEA20FE83A ON carpool_proof (pick_up_passenger_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CED7021172 ON carpool_proof (pick_up_driver_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE78B067B ON carpool_proof (drop_off_passenger_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEC76281BF ON carpool_proof (drop_off_driver_address_id)');
        $this->addSql('CREATE INDEX IDX_59B969CEC3423909 ON carpool_proof (driver_id)');
        $this->addSql('CREATE INDEX IDX_59B969CE4502E565 ON carpool_proof (passenger_id)');
        $this->addSql('ALTER TABLE ask ADD carpool_proof_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0FBF2A5E5 FOREIGN KEY (carpool_proof_id) REFERENCES carpool_proof (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6826EAE0FBF2A5E5 ON ask (carpool_proof_id)');
        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F5F5B7AF75');
        $this->addSql('DROP INDEX UNIQ_462CE4F5F5B7AF75 ON position');
        $this->addSql('ALTER TABLE position CHANGE address_id waypoint_id INT NOT NULL');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F57BB1FD97 FOREIGN KEY (waypoint_id) REFERENCES waypoint (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_462CE4F57BB1FD97 ON position (waypoint_id)');
        $this->addSql('ALTER TABLE waypoint ADD floating TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0FBF2A5E5');
        $this->addSql('DROP INDEX UNIQ_6826EAE0FBF2A5E5 ON ask');
        $this->addSql('ALTER TABLE ask DROP carpool_proof_id');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEA20FE83A');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CED7021172');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE78B067B');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEC76281BF');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEC3423909');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE4502E565');
        $this->addSql('DROP INDEX UNIQ_59B969CEA20FE83A ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CED7021172 ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CE78B067B ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEC76281BF ON carpool_proof');
        $this->addSql('DROP INDEX IDX_59B969CEC3423909 ON carpool_proof');
        $this->addSql('DROP INDEX IDX_59B969CE4502E565 ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof ADD ask_id INT NOT NULL, ADD pickup_passenger_address_id INT DEFAULT NULL, ADD pickup_driver_address_id INT DEFAULT NULL, ADD dropoff_passenger_address_id INT DEFAULT NULL, ADD dropoff_driver_address_id INT DEFAULT NULL, DROP pick_up_passenger_address_id, DROP pick_up_driver_address_id, DROP drop_off_passenger_address_id, DROP drop_off_driver_address_id, DROP driver_id, DROP passenger_id, DROP status, DROP pick_up_passenger_date, DROP pick_up_driver_date, DROP drop_off_passenger_date, DROP drop_off_driver_date, DROP geo_json_points');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE78E28472 FOREIGN KEY (pickup_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE83850A9D FOREIGN KEY (dropoff_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEB524543A FOREIGN KEY (dropoff_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEE3374B2 FOREIGN KEY (pickup_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE83850A9D ON carpool_proof (dropoff_passenger_address_id)');
        $this->addSql('CREATE INDEX IDX_59B969CEB93F8B63 ON carpool_proof (ask_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEE3374B2 ON carpool_proof (pickup_passenger_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEB524543A ON carpool_proof (dropoff_driver_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE78E28472 ON carpool_proof (pickup_driver_address_id)');
        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F57BB1FD97');
        $this->addSql('DROP INDEX UNIQ_462CE4F57BB1FD97 ON position');
        $this->addSql('ALTER TABLE position CHANGE waypoint_id address_id INT NOT NULL');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_462CE4F5F5B7AF75 ON position (address_id)');
        $this->addSql('ALTER TABLE waypoint DROP floating');
    }
}
