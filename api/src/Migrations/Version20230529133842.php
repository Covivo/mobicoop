<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230529133842 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect_commitment_request_log DROP FOREIGN KEY FK_B7F451C09A1887DC');
        $this->addSql('DROP INDEX IDX_B7F451C0D5C9896F ON mobconnect_commitment_request_log');
        $this->addSql('ALTER TABLE mobconnect_commitment_request_log ADD type INT NOT NULL COMMENT \'The type of log: stage of the process at which it occurs.\', DROP journey_id, CHANGE content content LONGTEXT DEFAULT NULL COMMENT \'Content returned by the mobConnect HTTP request.(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect_commitment_request_log ADD journey_id INT DEFAULT NULL, DROP type, CHANGE content content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'Content returned by the mobConnect HTTP request.\'');
        $this->addSql('ALTER TABLE mobconnect_commitment_request_log ADD CONSTRAINT FK_B7F451C09A1887DC FOREIGN KEY (subscription_id) REFERENCES mobconnect__long_distance_subscription (id)');
        $this->addSql('CREATE INDEX IDX_B7F451C0D5C9896F ON mobconnect_commitment_request_log (journey_id)');
    }
}
