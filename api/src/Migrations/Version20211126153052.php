<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211126153052 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE identity_proof (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, validator_id INT DEFAULT NULL, status SMALLINT NOT NULL, created_date DATETIME DEFAULT NULL, validated_date DATETIME DEFAULT NULL, refused_date DATETIME DEFAULT NULL, refusal_reason VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_11A20A2DA76ED395 (user_id), INDEX IDX_11A20A2DB0644AEC (validator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE identity_proof ADD CONSTRAINT FK_11A20A2DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE identity_proof ADD CONSTRAINT FK_11A20A2DB0644AEC FOREIGN KEY (validator_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE identity_proof');
    }
}
