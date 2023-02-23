<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230123125929 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user__delegate_authentication (id INT AUTO_INCREMENT NOT NULL, user_by_delegation_id INT DEFAULT NULL, delegate_user_id INT DEFAULT NULL, delegation_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX IDX_D3A002CD7741E9B3 (user_by_delegation_id), INDEX IDX_D3A002CD5744C683 (delegate_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user__delegate_authentication ADD CONSTRAINT FK_D3A002CD7741E9B3 FOREIGN KEY (user_by_delegation_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user__delegate_authentication ADD CONSTRAINT FK_D3A002CD5744C683 FOREIGN KEY (delegate_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user__delegate_authentication');
    }
}
