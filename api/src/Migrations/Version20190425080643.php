<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190425080643 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mass_matching (id INT AUTO_INCREMENT NOT NULL, mass_person1_id INT NOT NULL, mass_person2_id INT NOT NULL, direction_id INT DEFAULT NULL, co2 INT NOT NULL, UNIQUE INDEX UNIQ_9B3B75EB1AE2DA96 (mass_person1_id), UNIQUE INDEX UNIQ_9B3B75EB8577578 (mass_person2_id), INDEX IDX_9B3B75EBAF73D997 (direction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mass_person (id INT AUTO_INCREMENT NOT NULL, personal_address_id INT NOT NULL, work_address_id INT NOT NULL, mass_id INT NOT NULL, direction_id INT DEFAULT NULL, given_id VARCHAR(255) NOT NULL, given_name VARCHAR(255) NOT NULL, family_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_75908575B56708D5 (personal_address_id), UNIQUE INDEX UNIQ_75908575EED402A6 (work_address_id), UNIQUE INDEX UNIQ_75908575EA5DA7EB (mass_id), INDEX IDX_75908575AF73D997 (direction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mass_matching ADD CONSTRAINT FK_9B3B75EB1AE2DA96 FOREIGN KEY (mass_person1_id) REFERENCES mass_person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_matching ADD CONSTRAINT FK_9B3B75EB8577578 FOREIGN KEY (mass_person2_id) REFERENCES mass_person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_matching ADD CONSTRAINT FK_9B3B75EBAF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id)');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575B56708D5 FOREIGN KEY (personal_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575EED402A6 FOREIGN KEY (work_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575EA5DA7EB FOREIGN KEY (mass_id) REFERENCES mass (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575AF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id)');
        $this->addSql('ALTER TABLE mass ADD calculation_date DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_matching DROP FOREIGN KEY FK_9B3B75EB1AE2DA96');
        $this->addSql('ALTER TABLE mass_matching DROP FOREIGN KEY FK_9B3B75EB8577578');
        $this->addSql('DROP TABLE mass_matching');
        $this->addSql('DROP TABLE mass_person');
        $this->addSql('ALTER TABLE mass DROP calculation_date');
    }
}
