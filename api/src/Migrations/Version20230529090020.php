<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230529090020 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect_commitment_request_log ADD subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect_commitment_request_log ADD CONSTRAINT FK_B7F451C09A1887DC FOREIGN KEY (subscription_id) REFERENCES mobconnect__long_distance_subscription (id)');
        $this->addSql('CREATE INDEX IDX_B7F451C09A1887DC ON mobconnect_commitment_request_log (subscription_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect_commitment_request_log DROP FOREIGN KEY FK_B7F451C09A1887DC');
        $this->addSql('DROP INDEX IDX_B7F451C09A1887DC ON mobconnect_commitment_request_log');
        $this->addSql('ALTER TABLE mobconnect_commitment_request_log DROP subscription_id');
    }
}
