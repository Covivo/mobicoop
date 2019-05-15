<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190429152547 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_person ADD outward_time TIME DEFAULT NULL, ADD return_time TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD house_number VARCHAR(45) DEFAULT NULL, ADD street VARCHAR(255) DEFAULT NULL, ADD sub_locality VARCHAR(100) DEFAULT NULL, ADD local_admin VARCHAR(100) DEFAULT NULL, ADD county VARCHAR(100) DEFAULT NULL, ADD macro_county VARCHAR(100) DEFAULT NULL, ADD region VARCHAR(100) DEFAULT NULL, ADD macro_region VARCHAR(100) DEFAULT NULL, ADD country_code VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE address DROP house_number, DROP street, DROP sub_locality, DROP local_admin, DROP county, DROP macro_county, DROP region, DROP macro_region, DROP country_code');
        $this->addSql('ALTER TABLE mass_person DROP outward_time, DROP return_time');
    }
}
