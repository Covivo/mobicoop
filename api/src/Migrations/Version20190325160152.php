<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190325160152 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria ADD margin_duration INT DEFAULT NULL, ADD mon_margin_duration INT DEFAULT NULL, ADD tue_margin_duration INT DEFAULT NULL, ADD wed_margin_duration INT DEFAULT NULL, ADD thu_margin_duration INT DEFAULT NULL, ADD fri_margin_duration INT DEFAULT NULL, ADD sat_margin_duration INT DEFAULT NULL, ADD sun_margin_duration INT DEFAULT NULL, DROP mon_margin_time, DROP tue_margin_time, DROP wed_margin_time, DROP thu_margin_time, DROP fri_margin_time, DROP sat_margin_time, DROP sun_margin_time, DROP margin_time');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria ADD mon_margin_time INT DEFAULT NULL, ADD tue_margin_time INT DEFAULT NULL, ADD wed_margin_time INT DEFAULT NULL, ADD thu_margin_time INT DEFAULT NULL, ADD fri_margin_time INT DEFAULT NULL, ADD sat_margin_time INT DEFAULT NULL, ADD sun_margin_time INT DEFAULT NULL, ADD margin_time INT DEFAULT NULL, DROP margin_duration, DROP mon_margin_duration, DROP tue_margin_duration, DROP wed_margin_duration, DROP thu_margin_duration, DROP fri_margin_duration, DROP sat_margin_duration, DROP sun_margin_duration');
    }
}
