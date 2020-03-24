<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200324081140 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof ADD driver_id INT DEFAULT NULL, ADD passenger_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEC3423909 FOREIGN KEY (driver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE4502E565 FOREIGN KEY (passenger_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_59B969CEC3423909 ON carpool_proof (driver_id)');
        $this->addSql('CREATE INDEX IDX_59B969CE4502E565 ON carpool_proof (passenger_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEC3423909');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE4502E565');
        $this->addSql('DROP INDEX IDX_59B969CEC3423909 ON carpool_proof');
        $this->addSql('DROP INDEX IDX_59B969CE4502E565 ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof DROP driver_id, DROP passenger_id');
    }
}
