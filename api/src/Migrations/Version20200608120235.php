<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200608120235 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ptjourney ADD mass_person_id INT DEFAULT NULL, ADD distance_walk_from_home INT DEFAULT NULL, ADD duration_walk_from_home INT DEFAULT NULL, ADD distance_walk_from_work INT DEFAULT NULL, ADD duration_walk_from_work INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ptjourney ADD CONSTRAINT FK_6BCA51CD828090B4 FOREIGN KEY (mass_person_id) REFERENCES mass_person (id)');
        $this->addSql('CREATE INDEX IDX_6BCA51CD828090B4 ON ptjourney (mass_person_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ptjourney DROP FOREIGN KEY FK_6BCA51CD828090B4');
        $this->addSql('DROP INDEX IDX_6BCA51CD828090B4 ON ptjourney');
        $this->addSql('ALTER TABLE ptjourney DROP mass_person_id, DROP distance_walk_from_home, DROP duration_walk_from_home, DROP distance_walk_from_work, DROP duration_walk_from_work');
    }
}
