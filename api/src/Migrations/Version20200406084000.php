<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200406084000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE solidary_matching (id INT AUTO_INCREMENT NOT NULL, matching_id INT DEFAULT NULL, solidary_user_id INT DEFAULT NULL, solidary_id INT DEFAULT NULL, criteria_id INT DEFAULT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_A95C6411B39876B8 (matching_id), INDEX IDX_A95C6411815BD757 (solidary_user_id), INDEX IDX_A95C6411E92CE751 (solidary_id), UNIQUE INDEX UNIQ_A95C6411990BEA15 (criteria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE solidary_ask DROP INDEX IDX_1F77CFF6B77A2899, ADD UNIQUE INDEX UNIQ_1F77CFF6B77A2899 (solidary_solution_id)');
        $this->addSql('ALTER TABLE solidary_ask ADD ask_id INT DEFAULT NULL, ADD criteria_id INT NOT NULL');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F77CFF6B93F8B63 ON solidary_ask (ask_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F77CFF6990BEA15 ON solidary_ask (criteria_id)');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF43815BD757');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF43B39876B8');
        $this->addSql('DROP INDEX IDX_EA7FBF43815BD757 ON solidary_solution');
        $this->addSql('DROP INDEX IDX_EA7FBF43B39876B8 ON solidary_solution');
        $this->addSql('ALTER TABLE solidary_solution ADD solidary_matching_id INT DEFAULT NULL, DROP matching_id, DROP solidary_user_id');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF4318E9BFA2 FOREIGN KEY (solidary_matching_id) REFERENCES solidary_solution (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EA7FBF4318E9BFA2 ON solidary_solution (solidary_matching_id)');
        $this->addSql('ALTER TABLE structure ADD m_min_range_time TIME DEFAULT NULL, ADD m_max_range_time TIME DEFAULT NULL, ADD a_min_range_time TIME DEFAULT NULL, ADD a_max_range_time TIME DEFAULT NULL, ADD e_min_range_time TIME DEFAULT NULL, ADD e_max_range_time TIME DEFAULT NULL, CHANGE m_min_time m_min_time TIME DEFAULT NULL, CHANGE m_max_time m_max_time TIME DEFAULT NULL, CHANGE a_min_time a_min_time TIME DEFAULT NULL, CHANGE a_max_time a_max_time TIME DEFAULT NULL, CHANGE e_min_time e_min_time TIME DEFAULT NULL, CHANGE e_max_time e_max_time TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE solidary_matching');
        $this->addSql('ALTER TABLE solidary_ask DROP INDEX UNIQ_1F77CFF6B77A2899, ADD INDEX IDX_1F77CFF6B77A2899 (solidary_solution_id)');
        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6B93F8B63');
        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6990BEA15');
        $this->addSql('DROP INDEX UNIQ_1F77CFF6B93F8B63 ON solidary_ask');
        $this->addSql('DROP INDEX UNIQ_1F77CFF6990BEA15 ON solidary_ask');
        $this->addSql('ALTER TABLE solidary_ask DROP ask_id, DROP criteria_id');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF4318E9BFA2');
        $this->addSql('DROP INDEX UNIQ_EA7FBF4318E9BFA2 ON solidary_solution');
        $this->addSql('ALTER TABLE solidary_solution ADD solidary_user_id INT DEFAULT NULL, CHANGE solidary_matching_id matching_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF43815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF43B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('CREATE INDEX IDX_EA7FBF43815BD757 ON solidary_solution (solidary_user_id)');
        $this->addSql('CREATE INDEX IDX_EA7FBF43B39876B8 ON solidary_solution (matching_id)');
        $this->addSql('ALTER TABLE structure DROP m_min_range_time, DROP m_max_range_time, DROP a_min_range_time, DROP a_max_range_time, DROP e_min_range_time, DROP e_max_range_time, CHANGE m_min_time m_min_time DATETIME DEFAULT NULL, CHANGE m_max_time m_max_time DATETIME DEFAULT NULL, CHANGE a_min_time a_min_time DATETIME DEFAULT NULL, CHANGE a_max_time a_max_time DATETIME DEFAULT NULL, CHANGE e_min_time e_min_time DATETIME DEFAULT NULL, CHANGE e_max_time e_max_time DATETIME DEFAULT NULL');
    }
}
