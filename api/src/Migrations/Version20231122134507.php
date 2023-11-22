<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231122134507 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE gratuity_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, gratuity_campaign_id INT NOT NULL, created_date DATETIME NOT NULL, INDEX IDX_662A5FE9A76ED395 (user_id), INDEX IDX_662A5FE95E4A089D (gratuity_campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE gratuity_notification ADD CONSTRAINT FK_662A5FE9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gratuity_notification ADD CONSTRAINT FK_662A5FE95E4A089D FOREIGN KEY (gratuity_campaign_id) REFERENCES gratuity_campaign (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE gratuity_notification');
    }
}
