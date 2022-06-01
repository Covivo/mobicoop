<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200222085639 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE volunteer_service DROP FOREIGN KEY FK_3F7585EAED5CA9E6');
        $this->addSql('CREATE TABLE volunteer_need (volunteer_id INT NOT NULL, need_id INT NOT NULL, INDEX IDX_50457D688EFAB6B1 (volunteer_id), INDEX IDX_50457D68624AF264 (need_id), PRIMARY KEY(volunteer_id, need_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE structure_need (structure_id INT NOT NULL, need_id INT NOT NULL, INDEX IDX_57EE1E662534008B (structure_id), INDEX IDX_57EE1E66624AF264 (need_id), PRIMARY KEY(structure_id, need_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE structure_proof (id INT AUTO_INCREMENT NOT NULL, structure_id INT NOT NULL, label VARCHAR(255) NOT NULL, type SMALLINT NOT NULL, position SMALLINT NOT NULL, checkbox TINYINT(1) DEFAULT NULL, input TINYINT(1) DEFAULT NULL, `select` TINYINT(1) DEFAULT NULL, radio TINYINT(1) DEFAULT NULL, options LONGTEXT NOT NULL, `values` LONGTEXT NOT NULL, file TINYINT(1) DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_2E281B4B2534008B (structure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proof (id INT AUTO_INCREMENT NOT NULL, structure_proof_id INT NOT NULL, solidary_id INT DEFAULT NULL, volunteer_id INT DEFAULT NULL, value VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, size INT NOT NULL, mime_type VARCHAR(255) NOT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_FBF940DD860DC45F (structure_proof_id), INDEX IDX_FBF940DDE92CE751 (solidary_id), INDEX IDX_FBF940DD8EFAB6B1 (volunteer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE volunteer_need ADD CONSTRAINT FK_50457D688EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_need ADD CONSTRAINT FK_50457D68624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE structure_need ADD CONSTRAINT FK_57EE1E662534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE structure_need ADD CONSTRAINT FK_57EE1E66624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE structure_proof ADD CONSTRAINT FK_2E281B4B2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD860DC45F FOREIGN KEY (structure_proof_id) REFERENCES structure_proof (id)');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DDE92CE751 FOREIGN KEY (solidary_id) REFERENCES structure_proof (id)');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD8EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES structure_proof (id)');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE volunteer_service');
        $this->addSql('ALTER TABLE diary CHANGE updated_date updated_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE structure CHANGE created_date created_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE need DROP FOREIGN KEY FK_E6F46C442534008B');
        $this->addSql('DROP INDEX IDX_E6F46C442534008B ON need');
        $this->addSql('ALTER TABLE need ADD private TINYINT(1) DEFAULT NULL, CHANGE structure_id solidary_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C44E92CE751 FOREIGN KEY (solidary_id) REFERENCES structure_proof (id)');
        $this->addSql('CREATE INDEX IDX_E6F46C44E92CE751 ON need (solidary_id)');
        $this->addSql('ALTER TABLE solidary DROP assisted');
        $this->addSql('ALTER TABLE subject CHANGE structure_id structure_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE need DROP FOREIGN KEY FK_E6F46C44E92CE751');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD860DC45F');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DDE92CE751');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD8EFAB6B1');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, structure_id INT DEFAULT NULL, label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_E19D9AD22534008B (structure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE volunteer_service (volunteer_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_3F7585EA8EFAB6B1 (volunteer_id), INDEX IDX_3F7585EAED5CA9E6 (service_id), PRIMARY KEY(volunteer_id, service_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD22534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE volunteer_service ADD CONSTRAINT FK_3F7585EA8EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_service ADD CONSTRAINT FK_3F7585EAED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE volunteer_need');
        $this->addSql('DROP TABLE structure_need');
        $this->addSql('DROP TABLE structure_proof');
        $this->addSql('DROP TABLE proof');
        $this->addSql('ALTER TABLE diary CHANGE updated_date updated_date DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_E6F46C44E92CE751 ON need');
        $this->addSql('ALTER TABLE need DROP private, CHANGE solidary_id structure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C442534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('CREATE INDEX IDX_E6F46C442534008B ON need (structure_id)');
        $this->addSql('ALTER TABLE solidary ADD assisted TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE structure CHANGE created_date created_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE subject CHANGE structure_id structure_id INT DEFAULT NULL');
    }
}
