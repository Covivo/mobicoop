<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200304092712 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE carpool_proof (id INT AUTO_INCREMENT NOT NULL, ask_id INT NOT NULL, pickup_passenger_address_id INT DEFAULT NULL, pickup_driver_address_id INT DEFAULT NULL, dropoff_passenger_address_id INT DEFAULT NULL, dropoff_driver_address_id INT DEFAULT NULL, direction_id INT DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_59B969CEB93F8B63 (ask_id), UNIQUE INDEX UNIQ_59B969CEE3374B2 (pickup_passenger_address_id), UNIQUE INDEX UNIQ_59B969CE78E28472 (pickup_driver_address_id), UNIQUE INDEX UNIQ_59B969CE83850A9D (dropoff_passenger_address_id), UNIQUE INDEX UNIQ_59B969CEB524543A (dropoff_driver_address_id), UNIQUE INDEX UNIQ_59B969CEAF73D997 (direction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE position (id INT AUTO_INCREMENT NOT NULL, proposal_id INT DEFAULT NULL, address_id INT NOT NULL, direction_id INT DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_462CE4F5F4792058 (proposal_id), UNIQUE INDEX UNIQ_462CE4F5F5B7AF75 (address_id), INDEX IDX_462CE4F5AF73D997 (direction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEE3374B2 FOREIGN KEY (pickup_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE78E28472 FOREIGN KEY (pickup_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE83850A9D FOREIGN KEY (dropoff_passenger_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEB524543A FOREIGN KEY (dropoff_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEAF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5AF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposal ADD dynamic TINYINT(1) DEFAULT NULL, ADD active TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE waypoint ADD reached TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE carpool_proof');
        $this->addSql('DROP TABLE position');
        $this->addSql('ALTER TABLE proposal DROP dynamic, DROP active');
        $this->addSql('ALTER TABLE waypoint DROP reached');
    }
}
