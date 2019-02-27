<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190214171033 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria ADD tue_margin_time INT DEFAULT NULL, ADD wed_margin_time INT DEFAULT NULL, ADD thu_margin_time INT DEFAULT NULL, ADD fri_margin_time INT DEFAULT NULL, ADD sat_margin_time INT DEFAULT NULL, ADD sun_margin_time INT DEFAULT NULL, ADD price_km NUMERIC(4, 2) DEFAULT NULL, CHANGE margin_time mon_margin_time INT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal ADD comment LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE car ADD price_km NUMERIC(4, 2) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE car DROP price_km');
        $this->addSql('ALTER TABLE criteria ADD margin_time INT DEFAULT NULL, DROP mon_margin_time, DROP tue_margin_time, DROP wed_margin_time, DROP thu_margin_time, DROP fri_margin_time, DROP sat_margin_time, DROP sun_margin_time, DROP price_km');
        $this->addSql('ALTER TABLE proposal DROP comment');
    }
}
