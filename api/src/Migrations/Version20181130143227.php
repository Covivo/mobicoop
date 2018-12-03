<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181130143227 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, from_lat NUMERIC(10, 6) NOT NULL, to_lat NUMERIC(10, 6) NOT NULL, from_lon NUMERIC(10, 6) NOT NULL, to_lon NUMERIC(10, 6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE near (id INT AUTO_INCREMENT NOT NULL, zone1_id INT NOT NULL, zone2_id INT NOT NULL, INDEX IDX_764C1C1197F77BCC (zone1_id), INDEX IDX_764C1C118542D422 (zone2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE near ADD CONSTRAINT FK_764C1C1197F77BCC FOREIGN KEY (zone1_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE near ADD CONSTRAINT FK_764C1C118542D422 FOREIGN KEY (zone2_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE path DROP INDEX IDX_B548B0FEE74C799, ADD UNIQUE INDEX UNIQ_B548B0FEE74C799 (point1_id)');
        $this->addSql('ALTER TABLE path DROP INDEX IDX_B548B0FFCC16877, ADD UNIQUE INDEX UNIQ_B548B0FFCC16877 (point2_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE near DROP FOREIGN KEY FK_764C1C1197F77BCC');
        $this->addSql('ALTER TABLE near DROP FOREIGN KEY FK_764C1C118542D422');
        $this->addSql('DROP TABLE zone');
        $this->addSql('DROP TABLE near');
        $this->addSql('ALTER TABLE path DROP INDEX UNIQ_B548B0FEE74C799, ADD INDEX IDX_B548B0FEE74C799 (point1_id)');
        $this->addSql('ALTER TABLE path DROP INDEX UNIQ_B548B0FFCC16877, ADD INDEX IDX_B548B0FFCC16877 (point2_id)');
    }
}
