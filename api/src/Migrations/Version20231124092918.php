<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231124092918 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log ADD gratuity_campaign_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C55E4A089D FOREIGN KEY (gratuity_campaign_id) REFERENCES gratuity_campaign (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8F3F68C55E4A089D ON log (gratuity_campaign_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C55E4A089D');
        $this->addSql('DROP INDEX IDX_8F3F68C55E4A089D ON log');
        $this->addSql('ALTER TABLE log DROP gratuity_campaign_id');
    }
}
