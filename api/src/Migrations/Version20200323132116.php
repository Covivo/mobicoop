<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200323132116 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE78E28472');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE83850A9D');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEAF73D997');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEB524543A');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEE3374B2');
        $this->addSql('DROP INDEX UNIQ_59B969CE83850A9D ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEE3374B2 ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEB524543A ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CE78E28472 ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEAF73D997 ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof ADD pick_up_passenger_address_id INT DEFAULT NULL, ADD pick_up_driver_address_id INT DEFAULT NULL, ADD drop_off_passenger_address_id INT DEFAULT NULL, ADD drop_off_driver_address_id INT DEFAULT NULL, ADD direction_driver_id INT DEFAULT NULL, ADD direction_passenger_id INT DEFAULT NULL, ADD status SMALLINT NOT NULL, ADD pick_up_passenger_date DATETIME DEFAULT NULL, ADD pick_up_driver_date DATETIME DEFAULT NULL, ADD drop_off_passenger_date DATETIME DEFAULT NULL, ADD drop_off_driver_date DATETIME DEFAULT NULL, DROP pickup_passenger_address_id, DROP pickup_driver_address_id, DROP dropoff_passenger_address_id, DROP dropoff_driver_address_id, DROP direction_id');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEA20FE83A FOREIGN KEY (pick_up_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CED7021172 FOREIGN KEY (pick_up_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE78B067B FOREIGN KEY (drop_off_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEC76281BF FOREIGN KEY (drop_off_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEA862FD7E FOREIGN KEY (direction_driver_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE8044A959 FOREIGN KEY (direction_passenger_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEA20FE83A ON carpool_proof (pick_up_passenger_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CED7021172 ON carpool_proof (pick_up_driver_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE78B067B ON carpool_proof (drop_off_passenger_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEC76281BF ON carpool_proof (drop_off_driver_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEA862FD7E ON carpool_proof (direction_driver_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE8044A959 ON carpool_proof (direction_passenger_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEA20FE83A');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CED7021172');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE78B067B');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEC76281BF');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEA862FD7E');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE8044A959');
        $this->addSql('DROP INDEX UNIQ_59B969CEA20FE83A ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CED7021172 ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CE78B067B ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEC76281BF ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEA862FD7E ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CE8044A959 ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof ADD pickup_passenger_address_id INT DEFAULT NULL, ADD pickup_driver_address_id INT DEFAULT NULL, ADD dropoff_passenger_address_id INT DEFAULT NULL, ADD dropoff_driver_address_id INT DEFAULT NULL, ADD direction_id INT DEFAULT NULL, DROP pick_up_passenger_address_id, DROP pick_up_driver_address_id, DROP drop_off_passenger_address_id, DROP drop_off_driver_address_id, DROP direction_driver_id, DROP direction_passenger_id, DROP status, DROP pick_up_passenger_date, DROP pick_up_driver_date, DROP drop_off_passenger_date, DROP drop_off_driver_date');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE78E28472 FOREIGN KEY (pickup_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE83850A9D FOREIGN KEY (dropoff_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEAF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEB524543A FOREIGN KEY (dropoff_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEE3374B2 FOREIGN KEY (pickup_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE83850A9D ON carpool_proof (dropoff_passenger_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEE3374B2 ON carpool_proof (pickup_passenger_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEB524543A ON carpool_proof (dropoff_driver_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE78E28472 ON carpool_proof (pickup_driver_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEAF73D997 ON carpool_proof (direction_id)');
    }
}
