<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221124081548 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cee__long_distance (id INT AUTO_INCREMENT NOT NULL, given_name VARCHAR(255) NOT NULL, family_name VARCHAR(255) NOT NULL, driving_license_number VARCHAR(15) NOT NULL, street_address VARCHAR(255) NOT NULL, postal_code VARCHAR(15) NOT NULL, address_locality VARCHAR(100) NOT NULL, start_address_locality VARCHAR(100) NOT NULL, end_address_locality VARCHAR(100) NOT NULL, distance INT NOT NULL, carpoolers_number INT NOT NULL, start_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, end_date DATETIME DEFAULT NULL, standardized_operation_ref VARCHAR(15) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2EC6900C450FF010 (telephone), UNIQUE INDEX UNIQ_2EC6900CE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cee__short_distance (id INT AUTO_INCREMENT NOT NULL, given_name VARCHAR(255) NOT NULL, family_name VARCHAR(255) NOT NULL, driving_license_number VARCHAR(15) NOT NULL, street_address VARCHAR(255) NOT NULL, postal_code VARCHAR(15) NOT NULL, address_locality VARCHAR(100) NOT NULL, start_address_locality VARCHAR(100) NOT NULL, end_address_locality VARCHAR(100) NOT NULL, standardized_operation_ref VARCHAR(15) NOT NULL, distance INT NOT NULL, carpoolers_number INT NOT NULL, operator_user_id VARCHAR(255) NOT NULL, rpc_journey_id VARCHAR(255) NOT NULL, rpc_number_status VARCHAR(255) NOT NULL, start_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, end_date DATETIME DEFAULT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1F8526753A2B0BF6 (operator_user_id), UNIQUE INDEX UNIQ_1F852675E28704A8 (rpc_journey_id), UNIQUE INDEX UNIQ_1F852675450FF010 (telephone), UNIQUE INDEX UNIQ_1F852675E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE cee__long_distance');
        $this->addSql('DROP TABLE cee__short_distance');
    }
}
