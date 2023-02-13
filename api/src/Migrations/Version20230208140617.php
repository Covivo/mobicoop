<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230208140617 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__short_distance_journey ADD verification_status INT DEFAULT 0 COMMENT \'Status of verification\', ADD rank INT DEFAULT NULL COMMENT \'Rank of the journey for the user\'');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD verification_status INT DEFAULT 0 COMMENT \'Status of verification\', ADD rank INT DEFAULT NULL COMMENT \'Rank of the journey for the user\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP verification_status, DROP rank');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey DROP verification_status, DROP rank');
    }
}
