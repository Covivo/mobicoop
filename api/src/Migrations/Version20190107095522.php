<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190107095522 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ptline ADD direction VARCHAR(255) DEFAULT NULL, CHANGE origin origin VARCHAR(100) DEFAULT NULL, CHANGE destination destination VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE ptleg CHANGE ptline_id ptline_id INT DEFAULT NULL, CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ptstep CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ptjourney CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ptjourney CHANGE distance distance INT NOT NULL, CHANGE duration duration INT NOT NULL');
        $this->addSql('ALTER TABLE ptleg CHANGE ptline_id ptline_id INT NOT NULL, CHANGE distance distance INT NOT NULL, CHANGE duration duration INT NOT NULL');
        $this->addSql('ALTER TABLE ptline DROP direction, CHANGE origin origin VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE destination destination VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ptstep CHANGE distance distance INT NOT NULL, CHANGE duration duration INT NOT NULL');
    }
}
