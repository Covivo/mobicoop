<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220622125824 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE community ADD user_delegate_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE community ADD CONSTRAINT FK_1B60403323107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_1B60403323107D10 ON community (user_delegate_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE community DROP FOREIGN KEY FK_1B60403323107D10');
        $this->addSql('DROP INDEX IDX_1B60403323107D10 ON community');
        $this->addSql('ALTER TABLE community DROP user_delegate_id');
    }
}
