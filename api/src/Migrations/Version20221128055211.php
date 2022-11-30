<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221128055211 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mobconnect__long_distance_subscription (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, subscription_id VARCHAR(255) NOT NULL, status VARCHAR(10) DEFAULT NULL, given_name VARCHAR(255) NOT NULL, family_name VARCHAR(255) NOT NULL, driving_license_number VARCHAR(15) NOT NULL, street_address VARCHAR(255) NOT NULL, postal_code VARCHAR(15) NOT NULL, address_locality VARCHAR(100) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_C928E63E450FF010 (telephone), UNIQUE INDEX UNIQ_C928E63EE7927C74 (email), UNIQUE INDEX UNIQ_C928E63EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mobconnect__short_distance_subscription (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, subscription_id VARCHAR(255) NOT NULL, status VARCHAR(10) DEFAULT NULL, given_name VARCHAR(255) NOT NULL, family_name VARCHAR(255) NOT NULL, driving_license_number VARCHAR(15) NOT NULL, street_address VARCHAR(255) NOT NULL, postal_code VARCHAR(15) NOT NULL, address_locality VARCHAR(100) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_5D330FE5450FF010 (telephone), UNIQUE INDEX UNIQ_5D330FE5E7927C74 (email), UNIQUE INDEX UNIQ_5D330FE5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mobconnect__auth (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, access_token VARCHAR(255) DEFAULT NULL, authorization_code VARCHAR(255) DEFAULT NULL, refresh_token VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_4D709F93A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mobconnect__long_distance_journey (id INT AUTO_INCREMENT NOT NULL, long_distance_subscription_id INT DEFAULT NULL, start_address_locality VARCHAR(100) DEFAULT NULL, end_address_locality VARCHAR(100) DEFAULT NULL, distance INT DEFAULT NULL, carpoolers_number INT DEFAULT NULL, start_date VARCHAR(50) DEFAULT NULL, end_date VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX IDX_894AD28F3827AC9 (long_distance_subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mobconnect__short_distance_journey (id INT AUTO_INCREMENT NOT NULL, short_distance_subscription_id INT DEFAULT NULL, start_address_locality VARCHAR(100) DEFAULT NULL, end_address_locality VARCHAR(100) DEFAULT NULL, distance INT DEFAULT NULL, carpoolers_number INT DEFAULT NULL, operator_user_id VARCHAR(255) DEFAULT NULL, rpc_journey_id VARCHAR(255) DEFAULT NULL, rpc_number_status VARCHAR(255) DEFAULT NULL, start_date VARCHAR(50) DEFAULT NULL, end_date VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_68CFFDA63A2B0BF6 (operator_user_id), UNIQUE INDEX UNIQ_68CFFDA6E28704A8 (rpc_journey_id), INDEX IDX_68CFFDA6375AD4C6 (short_distance_subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD CONSTRAINT FK_C928E63EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD CONSTRAINT FK_5D330FE5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE mobconnect__auth ADD CONSTRAINT FK_4D709F93A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD CONSTRAINT FK_894AD28F3827AC9 FOREIGN KEY (long_distance_subscription_id) REFERENCES mobconnect__long_distance_subscription (id)');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey ADD CONSTRAINT FK_68CFFDA6375AD4C6 FOREIGN KEY (short_distance_subscription_id) REFERENCES mobconnect__short_distance_subscription (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP FOREIGN KEY FK_894AD28F3827AC9');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey DROP FOREIGN KEY FK_68CFFDA6375AD4C6');
        $this->addSql('DROP TABLE mobconnect__long_distance_subscription');
        $this->addSql('DROP TABLE mobconnect__short_distance_subscription');
        $this->addSql('DROP TABLE mobconnect__auth');
        $this->addSql('DROP TABLE mobconnect__long_distance_journey');
        $this->addSql('DROP TABLE mobconnect__short_distance_journey');
    }
}
