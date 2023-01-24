<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230123075602 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD verification_date DATETIME DEFAULT NULL, ADD reject_reason VARCHAR(255) DEFAULT NULL, ADD comment VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD verification_date DATETIME DEFAULT NULL, ADD reject_reason VARCHAR(255) DEFAULT NULL, ADD comment VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription DROP verification_date, DROP reject_reason, DROP comment');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription DROP verification_date, DROP reject_reason, DROP comment');
    }
}
