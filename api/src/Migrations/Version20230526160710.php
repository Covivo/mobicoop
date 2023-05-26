<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230526160710 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mobconnect_commitment_request_log (id INT AUTO_INCREMENT NOT NULL, code INT DEFAULT NULL COMMENT \'Code returned by the mobConnect HTTP request.\', content LONGTEXT DEFAULT NULL COMMENT \'Content returned by the mobConnect HTTP request.\', payload LONGTEXT DEFAULT NULL COMMENT \'Payload send by the mobConnect HTTP request.(DC2Type:json)\', created_date DATETIME DEFAULT CURRENT_TIMESTAMP, discr VARCHAR(255) NOT NULL, journey_id INT DEFAULT NULL, INDEX IDX_B7F451C0D5C9896F (journey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD commitment_journey TINYINT(1) DEFAULT NULL COMMENT \'Status of http request to mobConnect\'');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey ADD commitment_journey TINYINT(1) DEFAULT NULL COMMENT \'Status of http request to mobConnect\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE mobconnect_commitment_request_log');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP commitment_journey');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey DROP commitment_journey');
    }
}
