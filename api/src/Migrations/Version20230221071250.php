<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230221071250 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey CHANGE bonus_status bonus_status SMALLINT DEFAULT 0 NOT NULL COMMENT \'Bonus Status of the EEC form\'');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey CHANGE bonus_status bonus_status SMALLINT DEFAULT 0 NOT NULL COMMENT \'Bonus Status of the EEC form\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey CHANGE bonus_status bonus_status SMALLINT DEFAULT 1 NOT NULL COMMENT \'Bonus Status of the EEC form\'');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey CHANGE bonus_status bonus_status SMALLINT DEFAULT 1 NOT NULL COMMENT \'Bonus Status of the EEC form\'');
    }
}
