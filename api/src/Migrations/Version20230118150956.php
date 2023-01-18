<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230118150956 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bank_transfer (id INT AUTO_INCREMENT NOT NULL, recipient_id INT DEFAULT NULL, territory_id INT DEFAULT NULL, carpool_proof_id INT DEFAULT NULL, amount NUMERIC(10, 6) NOT NULL, details LONGTEXT DEFAULT NULL, status INT NOT NULL, batch_id VARCHAR(36) NOT NULL, error LONGTEXT DEFAULT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_7174B947E92F8F78 (recipient_id), INDEX IDX_7174B94773F74AD4 (territory_id), INDEX IDX_7174B947FBF2A5E5 (carpool_proof_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bank_transfer ADD CONSTRAINT FK_7174B947E92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE bank_transfer ADD CONSTRAINT FK_7174B94773F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE bank_transfer ADD CONSTRAINT FK_7174B947FBF2A5E5 FOREIGN KEY (carpool_proof_id) REFERENCES carpool_proof (id) ON DELETE SET NULL');
        $this->addSql('DROP TABLE bank_transfert');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bank_transfert (id INT AUTO_INCREMENT NOT NULL, recipient_id INT DEFAULT NULL, territory_id INT DEFAULT NULL, carpool_proof_id INT DEFAULT NULL, amount NUMERIC(10, 6) NOT NULL, details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status INT NOT NULL, batch_id VARCHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_6DA3FA22FBF2A5E5 (carpool_proof_id), INDEX IDX_6DA3FA22E92F8F78 (recipient_id), INDEX IDX_6DA3FA2273F74AD4 (territory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE bank_transfert ADD CONSTRAINT FK_6DA3FA2273F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE bank_transfert ADD CONSTRAINT FK_6DA3FA22E92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE bank_transfert ADD CONSTRAINT FK_6DA3FA22FBF2A5E5 FOREIGN KEY (carpool_proof_id) REFERENCES carpool_proof (id) ON DELETE SET NULL');
        $this->addSql('DROP TABLE bank_transfer');
    }
}
