<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200324094916 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE8044A959');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEA862FD7E');
        $this->addSql('DROP INDEX UNIQ_59B969CEA862FD7E ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CE8044A959 ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof ADD direction_id INT DEFAULT NULL, ADD geo_json_points LINESTRING DEFAULT NULL COMMENT \'(DC2Type:linestring)\', DROP direction_driver_id, DROP direction_passenger_id');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEAF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEAF73D997 ON carpool_proof (direction_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEAF73D997');
        $this->addSql('DROP INDEX UNIQ_59B969CEAF73D997 ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof ADD direction_passenger_id INT DEFAULT NULL, DROP geo_json_points, CHANGE direction_id direction_driver_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE8044A959 FOREIGN KEY (direction_passenger_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEA862FD7E FOREIGN KEY (direction_driver_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEA862FD7E ON carpool_proof (direction_driver_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE8044A959 ON carpool_proof (direction_passenger_id)');
    }
}
