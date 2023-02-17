<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230217111319 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD carpool_proof_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD CONSTRAINT FK_894AD28FFBF2A5E5 FOREIGN KEY (carpool_proof_id) REFERENCES carpool_proof (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_894AD28FFBF2A5E5 ON mobconnect__long_distance_journey (carpool_proof_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP FOREIGN KEY FK_894AD28FFBF2A5E5');
        $this->addSql('DROP INDEX UNIQ_894AD28FFBF2A5E5 ON mobconnect__long_distance_journey');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP carpool_proof_id');
    }
}
