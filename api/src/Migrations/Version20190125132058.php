<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190125132058 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image CHANGE crop_x1 crop_x1 INT DEFAULT NULL, CHANGE crop_y1 crop_y1 INT DEFAULT NULL, CHANGE crop_x2 crop_x2 INT DEFAULT NULL, CHANGE crop_y2 crop_y2 INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image CHANGE crop_x1 crop_x1 INT NOT NULL, CHANGE crop_y1 crop_y1 INT NOT NULL, CHANGE crop_x2 crop_x2 INT NOT NULL, CHANGE crop_y2 crop_y2 INT NOT NULL');
    }
}
