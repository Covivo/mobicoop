<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181001131028 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solicitation ADD solicitation_linked_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783293B97A1 FOREIGN KEY (solicitation_linked_id) REFERENCES solicitation (id)');
        $this->addSql('CREATE INDEX IDX_4FA96783293B97A1 ON solicitation (solicitation_linked_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solicitation DROP FOREIGN KEY FK_4FA96783293B97A1');
        $this->addSql('DROP INDEX IDX_4FA96783293B97A1 ON solicitation');
        $this->addSql('ALTER TABLE solicitation DROP solicitation_linked_id');
    }
}
