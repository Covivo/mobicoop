<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231212195855 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD maximum_journeys_number SMALLINT DEFAULT 3 NOT NULL, ADD validity_period_duration SMALLINT DEFAULT 3 NOT NULL');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD maximum_journeys_number SMALLINT DEFAULT 10 NOT NULL, ADD validity_period_duration SMALLINT DEFAULT 3 NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription DROP maximum_journeys_number, DROP validity_period_duration');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription DROP maximum_journeys_number, DROP validity_period_duration');
    }
}
