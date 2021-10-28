<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211007124202 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE territory_parent (child_id INT NOT NULL, parent_id INT NOT NULL, INDEX IDX_A88BE0CBDD62C21B (child_id), INDEX IDX_A88BE0CB727ACA70 (parent_id), PRIMARY KEY(child_id, parent_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territory_parent ADD CONSTRAINT FK_A88BE0CBDD62C21B FOREIGN KEY (child_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE territory_parent ADD CONSTRAINT FK_A88BE0CB727ACA70 FOREIGN KEY (parent_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE territory ADD admin_level INT DEFAULT NULL, ADD min_latitude NUMERIC(10, 6) DEFAULT NULL, ADD max_latitude NUMERIC(10, 6) DEFAULT NULL, ADD min_longitude NUMERIC(10, 6) DEFAULT NULL, ADD max_longitude NUMERIC(10, 6) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_LONGITUDE ON territory (min_longitude, max_longitude)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE territory_parent');
        $this->addSql('DROP INDEX IDX_LONGITUDE ON territory');
        $this->addSql('ALTER TABLE territory DROP admin_level, DROP min_latitude, DROP max_latitude, DROP min_longitude, DROP max_longitude');
        $this->addSql('ALTER TABLE user DROP gamification');
    }
}
