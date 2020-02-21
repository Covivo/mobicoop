<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200221164215 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE need (id INT AUTO_INCREMENT NOT NULL, solidary_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, private TINYINT(1) DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_E6F46C44E92CE751 (solidary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proof (id INT AUTO_INCREMENT NOT NULL, structure_proof_id INT NOT NULL, solidary_id INT DEFAULT NULL, volunteer_id INT DEFAULT NULL, value VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, size INT NOT NULL, mime_type VARCHAR(255) NOT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_FBF940DD860DC45F (structure_proof_id), INDEX IDX_FBF940DDE92CE751 (solidary_id), INDEX IDX_FBF940DD8EFAB6B1 (volunteer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solidary_need (solidary_id INT NOT NULL, need_id INT NOT NULL, INDEX IDX_12578830E92CE751 (solidary_id), INDEX IDX_12578830624AF264 (need_id), PRIMARY KEY(solidary_id, need_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE structure_need (structure_id INT NOT NULL, need_id INT NOT NULL, INDEX IDX_57EE1E662534008B (structure_id), INDEX IDX_57EE1E66624AF264 (need_id), PRIMARY KEY(structure_id, need_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE structure_proof (id INT AUTO_INCREMENT NOT NULL, structure_id INT NOT NULL, label VARCHAR(255) NOT NULL, type SMALLINT NOT NULL, position SMALLINT NOT NULL, checkbox TINYINT(1) DEFAULT NULL, input TINYINT(1) DEFAULT NULL, `select` TINYINT(1) DEFAULT NULL, radio TINYINT(1) DEFAULT NULL, options LONGTEXT NOT NULL, `values` LONGTEXT NOT NULL, file TINYINT(1) DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_2E281B4B2534008B (structure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer (id INT AUTO_INCREMENT NOT NULL, address_id INT NOT NULL, user_id INT NOT NULL, structure_id INT NOT NULL, m_min_time DATETIME DEFAULT NULL, m_max_time DATETIME DEFAULT NULL, a_min_time DATETIME DEFAULT NULL, a_max_time DATETIME DEFAULT NULL, e_min_time DATETIME DEFAULT NULL, e_max_time DATETIME DEFAULT NULL, m_mon TINYINT(1) DEFAULT NULL, a_mon TINYINT(1) DEFAULT NULL, e_mon TINYINT(1) DEFAULT NULL, m_tue TINYINT(1) DEFAULT NULL, a_tue TINYINT(1) DEFAULT NULL, e_tue TINYINT(1) DEFAULT NULL, m_wed TINYINT(1) DEFAULT NULL, a_wed TINYINT(1) DEFAULT NULL, e_wed TINYINT(1) DEFAULT NULL, m_thu TINYINT(1) DEFAULT NULL, a_thu TINYINT(1) DEFAULT NULL, e_thu TINYINT(1) DEFAULT NULL, m_fri TINYINT(1) DEFAULT NULL, a_fri TINYINT(1) DEFAULT NULL, e_fri TINYINT(1) DEFAULT NULL, m_sat TINYINT(1) DEFAULT NULL, a_sat TINYINT(1) DEFAULT NULL, e_sat TINYINT(1) DEFAULT NULL, m_sun TINYINT(1) DEFAULT NULL, a_sun TINYINT(1) DEFAULT NULL, e_sun TINYINT(1) DEFAULT NULL, max_distance INT NOT NULL, vehicle TINYINT(1) DEFAULT NULL, comment VARCHAR(255) NOT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_5140DEDBF5B7AF75 (address_id), UNIQUE INDEX UNIQ_5140DEDBA76ED395 (user_id), INDEX IDX_5140DEDB2534008B (structure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_need (volunteer_id INT NOT NULL, need_id INT NOT NULL, INDEX IDX_50457D688EFAB6B1 (volunteer_id), INDEX IDX_50457D68624AF264 (need_id), PRIMARY KEY(volunteer_id, need_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C44E92CE751 FOREIGN KEY (solidary_id) REFERENCES structure_proof (id)');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD860DC45F FOREIGN KEY (structure_proof_id) REFERENCES structure_proof (id)');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DDE92CE751 FOREIGN KEY (solidary_id) REFERENCES structure_proof (id)');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD8EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES structure_proof (id)');
        $this->addSql('ALTER TABLE solidary_need ADD CONSTRAINT FK_12578830E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_need ADD CONSTRAINT FK_12578830624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE structure_need ADD CONSTRAINT FK_57EE1E662534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE structure_need ADD CONSTRAINT FK_57EE1E66624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE structure_proof ADD CONSTRAINT FK_2E281B4B2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE volunteer ADD CONSTRAINT FK_5140DEDBF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer ADD CONSTRAINT FK_5140DEDBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer ADD CONSTRAINT FK_5140DEDB2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE volunteer_need ADD CONSTRAINT FK_50457D688EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_need ADD CONSTRAINT FK_50457D68624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE action CHANGE created_date created_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE diary ADD comment LONGTEXT DEFAULT NULL, ADD progression INT DEFAULT NULL, CHANGE updated_date updated_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE log ADD campaign_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5F639F774 ON log (campaign_id)');
        $this->addSql('ALTER TABLE article DROP i_frame');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FF639F774');
        $this->addSql('DROP INDEX IDX_C53D045FF639F774 ON image');
        $this->addSql('ALTER TABLE image DROP campaign_id');
        $this->addSql('ALTER TABLE solidary ADD structure_id INT NOT NULL, ADD subject_id INT NOT NULL, ADD regular_detail VARCHAR(255) NOT NULL, ADD deadline_date DATETIME DEFAULT NULL, DROP assisted, DROP structure, DROP subject');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA42534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA423EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id)');
        $this->addSql('CREATE INDEX IDX_1896BCA42534008B ON solidary (structure_id)');
        $this->addSql('CREATE INDEX IDX_1896BCA423EDC87 ON solidary (subject_id)');
        $this->addSql('ALTER TABLE solidary_matching ADD volunteer_id INT DEFAULT NULL, ADD comment VARCHAR(255) NOT NULL, CHANGE matching_id matching_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C64118EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id)');
        $this->addSql('CREATE INDEX IDX_A95C64118EFAB6B1 ON solidary_matching (volunteer_id)');
        $this->addSql('ALTER TABLE structure ADD structure_id INT DEFAULT NULL, ADD m_min_time DATETIME DEFAULT NULL, ADD m_max_time DATETIME DEFAULT NULL, ADD a_min_time DATETIME DEFAULT NULL, ADD a_max_time DATETIME DEFAULT NULL, ADD e_min_time DATETIME DEFAULT NULL, ADD e_max_time DATETIME DEFAULT NULL, ADD m_mon TINYINT(1) DEFAULT NULL, ADD a_mon TINYINT(1) DEFAULT NULL, ADD e_mon TINYINT(1) DEFAULT NULL, ADD m_tue TINYINT(1) DEFAULT NULL, ADD a_tue TINYINT(1) DEFAULT NULL, ADD e_tue TINYINT(1) DEFAULT NULL, ADD m_wed TINYINT(1) DEFAULT NULL, ADD a_wed TINYINT(1) DEFAULT NULL, ADD e_wed TINYINT(1) DEFAULT NULL, ADD m_thu TINYINT(1) DEFAULT NULL, ADD a_thu TINYINT(1) DEFAULT NULL, ADD e_thu TINYINT(1) DEFAULT NULL, ADD m_fri TINYINT(1) DEFAULT NULL, ADD a_fri TINYINT(1) DEFAULT NULL, ADD e_fri TINYINT(1) DEFAULT NULL, ADD m_sat TINYINT(1) DEFAULT NULL, ADD a_sat TINYINT(1) DEFAULT NULL, ADD e_sat TINYINT(1) DEFAULT NULL, ADD m_sun TINYINT(1) DEFAULT NULL, ADD a_sun TINYINT(1) DEFAULT NULL, ADD e_sun TINYINT(1) DEFAULT NULL, CHANGE created_date created_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('CREATE INDEX IDX_6F0137EA2534008B ON structure (structure_id)');
        $this->addSql('ALTER TABLE subject ADD structure_id INT NOT NULL');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('CREATE INDEX IDX_FBCE3E7A2534008B ON subject (structure_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64923107D10');
        $this->addSql('DROP INDEX IDX_8D93D64923107D10 ON user');
        $this->addSql('ALTER TABLE user DROP user_delegate_id, DROP unsubscribe_token, DROP unsubscribe_date, CHANGE given_name given_name VARCHAR(100) DEFAULT NULL, CHANGE family_name family_name VARCHAR(100) DEFAULT NULL, CHANGE email email VARCHAR(100) NOT NULL, CHANGE password password VARCHAR(100) DEFAULT NULL, CHANGE nationality nationality VARCHAR(100) DEFAULT NULL, CHANGE telephone telephone VARCHAR(100) DEFAULT NULL, CHANGE pwd_token pwd_token VARCHAR(100) DEFAULT NULL, CHANGE geo_token geo_token VARCHAR(100) DEFAULT NULL, CHANGE validated_date_token validated_date_token VARCHAR(100) DEFAULT NULL, CHANGE facebook_id facebook_id VARCHAR(100) DEFAULT NULL, CHANGE phone_token phone_token VARCHAR(100) DEFAULT NULL, CHANGE ios_app_id ios_app_id VARCHAR(100) DEFAULT NULL, CHANGE android_app_id android_app_id VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_need DROP FOREIGN KEY FK_12578830624AF264');
        $this->addSql('ALTER TABLE structure_need DROP FOREIGN KEY FK_57EE1E66624AF264');
        $this->addSql('ALTER TABLE volunteer_need DROP FOREIGN KEY FK_50457D68624AF264');
        $this->addSql('ALTER TABLE need DROP FOREIGN KEY FK_E6F46C44E92CE751');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD860DC45F');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DDE92CE751');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD8EFAB6B1');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C64118EFAB6B1');
        $this->addSql('ALTER TABLE volunteer_need DROP FOREIGN KEY FK_50457D688EFAB6B1');
        $this->addSql('DROP TABLE need');
        $this->addSql('DROP TABLE proof');
        $this->addSql('DROP TABLE solidary_need');
        $this->addSql('DROP TABLE structure_need');
        $this->addSql('DROP TABLE structure_proof');
        $this->addSql('DROP TABLE volunteer');
        $this->addSql('DROP TABLE volunteer_need');
        $this->addSql('ALTER TABLE action CHANGE created_date created_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD i_frame VARCHAR(512) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE diary DROP comment, DROP progression, CHANGE updated_date updated_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD campaign_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FF639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('CREATE INDEX IDX_C53D045FF639F774 ON image (campaign_id)');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5F639F774');
        $this->addSql('DROP INDEX IDX_8F3F68C5F639F774 ON log');
        $this->addSql('ALTER TABLE log DROP campaign_id');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA42534008B');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA423EDC87');
        $this->addSql('DROP INDEX IDX_1896BCA42534008B ON solidary');
        $this->addSql('DROP INDEX IDX_1896BCA423EDC87 ON solidary');
        $this->addSql('ALTER TABLE solidary ADD assisted TINYINT(1) DEFAULT NULL, ADD subject VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP structure_id, DROP subject_id, DROP deadline_date, CHANGE regular_detail structure VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('DROP INDEX IDX_A95C64118EFAB6B1 ON solidary_matching');
        $this->addSql('ALTER TABLE solidary_matching DROP volunteer_id, DROP comment, CHANGE matching_id matching_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EA2534008B');
        $this->addSql('DROP INDEX IDX_6F0137EA2534008B ON structure');
        $this->addSql('ALTER TABLE structure DROP structure_id, DROP m_min_time, DROP m_max_time, DROP a_min_time, DROP a_max_time, DROP e_min_time, DROP e_max_time, DROP m_mon, DROP a_mon, DROP e_mon, DROP m_tue, DROP a_tue, DROP e_tue, DROP m_wed, DROP a_wed, DROP e_wed, DROP m_thu, DROP a_thu, DROP e_thu, DROP m_fri, DROP a_fri, DROP e_fri, DROP m_sat, DROP a_sat, DROP e_sat, DROP m_sun, DROP a_sun, DROP e_sun, CHANGE created_date created_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7A2534008B');
        $this->addSql('DROP INDEX IDX_FBCE3E7A2534008B ON subject');
        $this->addSql('ALTER TABLE subject DROP structure_id');
        $this->addSql('ALTER TABLE user ADD user_delegate_id INT DEFAULT NULL, ADD unsubscribe_token VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD unsubscribe_date DATETIME DEFAULT NULL, CHANGE given_name given_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE family_name family_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE password password VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE nationality nationality VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE telephone telephone VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE validated_date_token validated_date_token VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE pwd_token pwd_token VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE geo_token geo_token VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE phone_token phone_token VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE ios_app_id ios_app_id VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE android_app_id android_app_id VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE facebook_id facebook_id VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64923107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64923107D10 ON user (user_delegate_id)');
    }
}
