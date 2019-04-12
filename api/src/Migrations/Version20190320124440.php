<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190320124440 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria ADD strict_date TINYINT(1) NOT NULL, ADD max_detour_time INT DEFAULT NULL, ADD max_detour_distance INT DEFAULT NULL, DROP max_deviation_time, DROP max_deviation_distance');
        $this->addSql('ALTER TABLE user ADD max_detour_time INT DEFAULT NULL, ADD max_detour_distance INT DEFAULT NULL, DROP max_deviation_time, DROP max_deviation_distance');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria ADD max_deviation_time INT DEFAULT NULL, ADD max_deviation_distance INT DEFAULT NULL, DROP strict_date, DROP max_detour_time, DROP max_detour_distance');
        $this->addSql('ALTER TABLE user ADD max_deviation_time INT DEFAULT NULL, ADD max_deviation_distance INT DEFAULT NULL, DROP max_detour_time, DROP max_detour_distance');
    }
}
