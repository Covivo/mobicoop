<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190122154548 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, address_id INT NOT NULL, name VARCHAR(255) NOT NULL, status SMALLINT NOT NULL, description VARCHAR(255) NOT NULL, full_description LONGTEXT NOT NULL, from_date DATETIME NOT NULL, to_date DATETIME NOT NULL, use_time TINYINT(1) NOT NULL, url VARCHAR(255) DEFAULT NULL, INDEX IDX_3BAE0AA7A76ED395 (user_id), UNIQUE INDEX UNIQ_3BAE0AA7F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, image_type_id INT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, alt VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) NOT NULL, encoding_format VARCHAR(255) NOT NULL, position SMALLINT NOT NULL, INDEX IDX_C53D045F71F7E88B (event_id), INDEX IDX_C53D045F505CDB4F (image_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, folder VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_type_thumbnail_type (image_type_id INT NOT NULL, thumbnail_type_id INT NOT NULL, INDEX IDX_834E46D0505CDB4F (image_type_id), INDEX IDX_834E46D0E7E8E7EA (thumbnail_type_id), PRIMARY KEY(image_type_id, thumbnail_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thumbnail_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(45) DEFAULT NULL, size INT NOT NULL, encoding_format VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F505CDB4F FOREIGN KEY (image_type_id) REFERENCES image_type (id)');
        $this->addSql('ALTER TABLE image_type_thumbnail_type ADD CONSTRAINT FK_834E46D0505CDB4F FOREIGN KEY (image_type_id) REFERENCES image_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image_type_thumbnail_type ADD CONSTRAINT FK_834E46D0E7E8E7EA FOREIGN KEY (thumbnail_type_id) REFERENCES thumbnail_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F71F7E88B');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F505CDB4F');
        $this->addSql('ALTER TABLE image_type_thumbnail_type DROP FOREIGN KEY FK_834E46D0505CDB4F');
        $this->addSql('ALTER TABLE image_type_thumbnail_type DROP FOREIGN KEY FK_834E46D0E7E8E7EA');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE image_type');
        $this->addSql('DROP TABLE image_type_thumbnail_type');
        $this->addSql('DROP TABLE thumbnail_type');
    }
}
