<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200319093810 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE218E9BFA2');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD8EFAB6B1');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C64118EFAB6B1');
        $this->addSql('ALTER TABLE volunteer_need DROP FOREIGN KEY FK_50457D688EFAB6B1');
        $this->addSql('CREATE TABLE solidary_ask (id INT AUTO_INCREMENT NOT NULL, solidary_solution_id INT NOT NULL, status SMALLINT NOT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_1F77CFF6B77A2899 (solidary_solution_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solidary_ask_history (id INT AUTO_INCREMENT NOT NULL, solidary_ask_id INT DEFAULT NULL, message_id INT DEFAULT NULL, status SMALLINT NOT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_A4345EB3550BE553 (solidary_ask_id), UNIQUE INDEX UNIQ_A4345EB3537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solidary_solution (id INT AUTO_INCREMENT NOT NULL, matching_id INT DEFAULT NULL, solidary_id INT NOT NULL, solidary_user_id INT NOT NULL, comment VARCHAR(255) NOT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_EA7FBF43B39876B8 (matching_id), INDEX IDX_EA7FBF43E92CE751 (solidary_id), INDEX IDX_EA7FBF43815BD757 (solidary_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solidary_user (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, user_id INT NOT NULL, beneficiary TINYINT(1) DEFAULT NULL, volunteer TINYINT(1) DEFAULT NULL, m_min_time DATETIME DEFAULT NULL, m_max_time DATETIME DEFAULT NULL, a_min_time DATETIME DEFAULT NULL, a_max_time DATETIME DEFAULT NULL, e_min_time DATETIME DEFAULT NULL, e_max_time DATETIME DEFAULT NULL, m_mon TINYINT(1) DEFAULT NULL, a_mon TINYINT(1) DEFAULT NULL, e_mon TINYINT(1) DEFAULT NULL, m_tue TINYINT(1) DEFAULT NULL, a_tue TINYINT(1) DEFAULT NULL, e_tue TINYINT(1) DEFAULT NULL, m_wed TINYINT(1) DEFAULT NULL, a_wed TINYINT(1) DEFAULT NULL, e_wed TINYINT(1) DEFAULT NULL, m_thu TINYINT(1) DEFAULT NULL, a_thu TINYINT(1) DEFAULT NULL, e_thu TINYINT(1) DEFAULT NULL, m_fri TINYINT(1) DEFAULT NULL, a_fri TINYINT(1) DEFAULT NULL, e_fri TINYINT(1) DEFAULT NULL, m_sat TINYINT(1) DEFAULT NULL, a_sat TINYINT(1) DEFAULT NULL, e_sat TINYINT(1) DEFAULT NULL, m_sun TINYINT(1) DEFAULT NULL, a_sun TINYINT(1) DEFAULT NULL, e_sun TINYINT(1) DEFAULT NULL, max_distance INT DEFAULT NULL, vehicle TINYINT(1) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_7930323DF5B7AF75 (address_id), UNIQUE INDEX UNIQ_7930323DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solidary_user_need (solidary_user_id INT NOT NULL, need_id INT NOT NULL, INDEX IDX_62D4776D815BD757 (solidary_user_id), INDEX IDX_62D4776D624AF264 (need_id), PRIMARY KEY(solidary_user_id, need_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solidary_user_structure (id INT AUTO_INCREMENT NOT NULL, solidary_user_id INT NOT NULL, structure_id INT NOT NULL, created_date DATETIME DEFAULT NULL, INDEX IDX_436AB1AE815BD757 (solidary_user_id), INDEX IDX_436AB1AE2534008B (structure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6B77A2899 FOREIGN KEY (solidary_solution_id) REFERENCES solidary_solution (id)');
        $this->addSql('ALTER TABLE solidary_ask_history ADD CONSTRAINT FK_A4345EB3550BE553 FOREIGN KEY (solidary_ask_id) REFERENCES solidary_ask (id)');
        $this->addSql('ALTER TABLE solidary_ask_history ADD CONSTRAINT FK_A4345EB3537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF43B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF43E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF43815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('ALTER TABLE solidary_user ADD CONSTRAINT FK_7930323DF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_user ADD CONSTRAINT FK_7930323DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_user_need ADD CONSTRAINT FK_62D4776D815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_user_need ADD CONSTRAINT FK_62D4776D624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_user_structure ADD CONSTRAINT FK_436AB1AE815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('ALTER TABLE solidary_user_structure ADD CONSTRAINT FK_436AB1AE2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('DROP TABLE solidary_matching');
        $this->addSql('DROP TABLE volunteer');
        $this->addSql('DROP TABLE volunteer_need');
        $this->addSql('DROP INDEX IDX_917BEDE218E9BFA2 ON diary');
        $this->addSql('ALTER TABLE diary CHANGE solidary_matching_id solidary_solution_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2B77A2899 FOREIGN KEY (solidary_solution_id) REFERENCES solidary_solution (id)');
        $this->addSql('CREATE INDEX IDX_917BEDE2B77A2899 ON diary (solidary_solution_id)');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DDE92CE751');
        $this->addSql('DROP INDEX IDX_FBF940DDE92CE751 ON proof');
        $this->addSql('DROP INDEX IDX_FBF940DD8EFAB6B1 ON proof');
        $this->addSql('ALTER TABLE proof ADD solidary_user_structure_id INT NOT NULL, DROP solidary_id, DROP volunteer_id');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DDE51EA2A6 FOREIGN KEY (solidary_user_structure_id) REFERENCES solidary_user_structure (id)');
        $this->addSql('CREATE INDEX IDX_FBF940DDE51EA2A6 ON proof (solidary_user_structure_id)');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4A76ED395');
        $this->addSql('DROP INDEX IDX_1896BCA4A76ED395 ON solidary');
        $this->addSql('ALTER TABLE solidary CHANGE user_id solidary_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('CREATE INDEX IDX_1896BCA4815BD757 ON solidary (solidary_user_id)');
        $this->addSql('ALTER TABLE user ADD solidary_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649815BD757 ON user (solidary_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_ask_history DROP FOREIGN KEY FK_A4345EB3550BE553');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2B77A2899');
        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6B77A2899');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4815BD757');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF43815BD757');
        $this->addSql('ALTER TABLE solidary_user_need DROP FOREIGN KEY FK_62D4776D815BD757');
        $this->addSql('ALTER TABLE solidary_user_structure DROP FOREIGN KEY FK_436AB1AE815BD757');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649815BD757');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DDE51EA2A6');
        $this->addSql('CREATE TABLE solidary_matching (id INT AUTO_INCREMENT NOT NULL, matching_id INT DEFAULT NULL, solidary_id INT NOT NULL, volunteer_id INT DEFAULT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, comment VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_A95C6411E92CE751 (solidary_id), INDEX IDX_A95C64118EFAB6B1 (volunteer_id), INDEX IDX_A95C6411B39876B8 (matching_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE volunteer (id INT AUTO_INCREMENT NOT NULL, address_id INT NOT NULL, user_id INT NOT NULL, structure_id INT NOT NULL, m_min_time DATETIME DEFAULT NULL, m_max_time DATETIME DEFAULT NULL, a_min_time DATETIME DEFAULT NULL, a_max_time DATETIME DEFAULT NULL, e_min_time DATETIME DEFAULT NULL, e_max_time DATETIME DEFAULT NULL, m_mon TINYINT(1) DEFAULT NULL, a_mon TINYINT(1) DEFAULT NULL, e_mon TINYINT(1) DEFAULT NULL, m_tue TINYINT(1) DEFAULT NULL, a_tue TINYINT(1) DEFAULT NULL, e_tue TINYINT(1) DEFAULT NULL, m_wed TINYINT(1) DEFAULT NULL, a_wed TINYINT(1) DEFAULT NULL, e_wed TINYINT(1) DEFAULT NULL, m_thu TINYINT(1) DEFAULT NULL, a_thu TINYINT(1) DEFAULT NULL, e_thu TINYINT(1) DEFAULT NULL, m_fri TINYINT(1) DEFAULT NULL, a_fri TINYINT(1) DEFAULT NULL, e_fri TINYINT(1) DEFAULT NULL, m_sat TINYINT(1) DEFAULT NULL, a_sat TINYINT(1) DEFAULT NULL, e_sat TINYINT(1) DEFAULT NULL, m_sun TINYINT(1) DEFAULT NULL, a_sun TINYINT(1) DEFAULT NULL, e_sun TINYINT(1) DEFAULT NULL, max_distance INT NOT NULL, vehicle TINYINT(1) DEFAULT NULL, comment VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_5140DEDBA76ED395 (user_id), INDEX IDX_5140DEDB2534008B (structure_id), UNIQUE INDEX UNIQ_5140DEDBF5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE volunteer_need (volunteer_id INT NOT NULL, need_id INT NOT NULL, INDEX IDX_50457D688EFAB6B1 (volunteer_id), INDEX IDX_50457D68624AF264 (need_id), PRIMARY KEY(volunteer_id, need_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C64118EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
        $this->addSql('ALTER TABLE volunteer ADD CONSTRAINT FK_5140DEDB2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE volunteer ADD CONSTRAINT FK_5140DEDBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer ADD CONSTRAINT FK_5140DEDBF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_need ADD CONSTRAINT FK_50457D68624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_need ADD CONSTRAINT FK_50457D688EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE solidary_ask');
        $this->addSql('DROP TABLE solidary_ask_history');
        $this->addSql('DROP TABLE solidary_solution');
        $this->addSql('DROP TABLE solidary_user');
        $this->addSql('DROP TABLE solidary_user_need');
        $this->addSql('DROP TABLE solidary_user_structure');
        $this->addSql('DROP INDEX IDX_917BEDE2B77A2899 ON diary');
        $this->addSql('ALTER TABLE diary CHANGE solidary_solution_id solidary_matching_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE218E9BFA2 FOREIGN KEY (solidary_matching_id) REFERENCES solidary_matching (id)');
        $this->addSql('CREATE INDEX IDX_917BEDE218E9BFA2 ON diary (solidary_matching_id)');
        $this->addSql('DROP INDEX IDX_FBF940DDE51EA2A6 ON proof');
        $this->addSql('ALTER TABLE proof ADD solidary_id INT DEFAULT NULL, ADD volunteer_id INT DEFAULT NULL, DROP solidary_user_structure_id');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD8EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id)');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DDE92CE751 FOREIGN KEY (solidary_id) REFERENCES structure_proof (id)');
        $this->addSql('CREATE INDEX IDX_FBF940DDE92CE751 ON proof (solidary_id)');
        $this->addSql('CREATE INDEX IDX_FBF940DD8EFAB6B1 ON proof (volunteer_id)');
        $this->addSql('DROP INDEX IDX_1896BCA4815BD757 ON solidary');
        $this->addSql('ALTER TABLE solidary CHANGE solidary_user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1896BCA4A76ED395 ON solidary (user_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649815BD757 ON user');
        $this->addSql('ALTER TABLE user DROP solidary_user_id');
    }
}
