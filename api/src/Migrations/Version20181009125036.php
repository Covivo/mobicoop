<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181009125036 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE point DROP INDEX IDX_B7A5F324F5B7AF75, ADD UNIQUE INDEX UNIQ_B7A5F324F5B7AF75 (address_id)');
        $this->addSql('ALTER TABLE point DROP FOREIGN KEY FK_B7A5F324F5B7AF75');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472558C41CB');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947263826222');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472558C41CB FOREIGN KEY (proposal_linked_journey_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947263826222 FOREIGN KEY (proposal_linked_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solicitation DROP INDEX IDX_4FA96783232B2E93, ADD UNIQUE INDEX UNIQ_4FA96783232B2E93 (address_from_id)');
        $this->addSql('ALTER TABLE solicitation DROP INDEX IDX_4FA967837903D45, ADD UNIQUE INDEX UNIQ_4FA967837903D45 (address_to_id)');
        $this->addSql('ALTER TABLE solicitation DROP INDEX IDX_4FA96783293B97A1, ADD UNIQUE INDEX UNIQ_4FA96783293B97A1 (solicitation_linked_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE point DROP INDEX UNIQ_B7A5F324F5B7AF75, ADD INDEX IDX_B7A5F324F5B7AF75 (address_id)');
        $this->addSql('ALTER TABLE point DROP FOREIGN KEY FK_B7A5F324F5B7AF75');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947263826222');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472558C41CB');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947263826222 FOREIGN KEY (proposal_linked_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472558C41CB FOREIGN KEY (proposal_linked_journey_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE solicitation DROP INDEX UNIQ_4FA96783232B2E93, ADD INDEX IDX_4FA96783232B2E93 (address_from_id)');
        $this->addSql('ALTER TABLE solicitation DROP INDEX UNIQ_4FA967837903D45, ADD INDEX IDX_4FA967837903D45 (address_to_id)');
        $this->addSql('ALTER TABLE solicitation DROP INDEX UNIQ_4FA96783293B97A1, ADD INDEX IDX_4FA96783293B97A1 (solicitation_linked_id)');
    }
}
