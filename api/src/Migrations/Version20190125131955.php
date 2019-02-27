<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190125131955 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F505CDB4F');
        $this->addSql('ALTER TABLE image_type_thumbnail_type DROP FOREIGN KEY FK_834E46D0505CDB4F');
        $this->addSql('ALTER TABLE image_type_thumbnail_type DROP FOREIGN KEY FK_834E46D0E7E8E7EA');
        $this->addSql('DROP TABLE image_type');
        $this->addSql('DROP TABLE image_type_thumbnail_type');
        $this->addSql('DROP TABLE thumbnail_type');
        $this->addSql('DROP INDEX IDX_C53D045F505CDB4F ON image');
        $this->addSql('ALTER TABLE image ADD crop_x1 INT NOT NULL, ADD crop_y1 INT NOT NULL, ADD crop_x2 INT NOT NULL, ADD crop_y2 INT NOT NULL, DROP image_type_id, CHANGE width width INT NOT NULL, CHANGE height height INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE image_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, folder VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_type_thumbnail_type (image_type_id INT NOT NULL, thumbnail_type_id INT NOT NULL, INDEX IDX_834E46D0505CDB4F (image_type_id), INDEX IDX_834E46D0E7E8E7EA (thumbnail_type_id), PRIMARY KEY(image_type_id, thumbnail_type_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thumbnail_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(45) DEFAULT NULL COLLATE utf8mb4_unicode_ci, size INT NOT NULL, encoding_format VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image_type_thumbnail_type ADD CONSTRAINT FK_834E46D0505CDB4F FOREIGN KEY (image_type_id) REFERENCES image_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image_type_thumbnail_type ADD CONSTRAINT FK_834E46D0E7E8E7EA FOREIGN KEY (thumbnail_type_id) REFERENCES thumbnail_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD image_type_id INT DEFAULT NULL, DROP crop_x1, DROP crop_y1, DROP crop_x2, DROP crop_y2, CHANGE width width INT DEFAULT NULL, CHANGE height height INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F505CDB4F FOREIGN KEY (image_type_id) REFERENCES image_type (id)');
        $this->addSql('CREATE INDEX IDX_C53D045F505CDB4F ON image (image_type_id)');
    }
}
