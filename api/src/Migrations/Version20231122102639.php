<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231122102639 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE gratuity_campaign (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, template VARCHAR(255) NOT NULL, status INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_4B03BBE9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gratuity_campaign_territory (gratuity_campaign_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_6DADC6E05E4A089D (gratuity_campaign_id), INDEX IDX_6DADC6E073F74AD4 (territory_id), PRIMARY KEY(gratuity_campaign_id, territory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE gratuity_campaign ADD CONSTRAINT FK_4B03BBE9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gratuity_campaign_territory ADD CONSTRAINT FK_6DADC6E05E4A089D FOREIGN KEY (gratuity_campaign_id) REFERENCES gratuity_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gratuity_campaign_territory ADD CONSTRAINT FK_6DADC6E073F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE gratuity_campaign_territory DROP FOREIGN KEY FK_6DADC6E05E4A089D');
        $this->addSql('DROP TABLE gratuity_campaign');
        $this->addSql('DROP TABLE gratuity_campaign_territory');
    }
}
