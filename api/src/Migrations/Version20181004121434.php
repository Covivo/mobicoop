<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181004121434 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proposal DROP INDEX IDX_BFE5947263826222, ADD UNIQUE INDEX UNIQ_BFE5947263826222 (proposal_linked_id)');
        $this->addSql('ALTER TABLE proposal DROP INDEX IDX_BFE59472558C41CB, ADD UNIQUE INDEX UNIQ_BFE59472558C41CB (proposal_linked_journey_id)');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472990BEA15');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289990BEA15');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solicitation DROP FOREIGN KEY FK_4FA96783990BEA15');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_address DROP INDEX IDX_5543718BF5B7AF75, ADD UNIQUE INDEX UNIQ_5543718BF5B7AF75 (address_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289990BEA15');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE proposal DROP INDEX UNIQ_BFE5947263826222, ADD INDEX IDX_BFE5947263826222 (proposal_linked_id)');
        $this->addSql('ALTER TABLE proposal DROP INDEX UNIQ_BFE59472558C41CB, ADD INDEX IDX_BFE59472558C41CB (proposal_linked_journey_id)');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472990BEA15');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE solicitation DROP FOREIGN KEY FK_4FA96783990BEA15');
        $this->addSql('ALTER TABLE solicitation ADD CONSTRAINT FK_4FA96783990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE user_address DROP INDEX UNIQ_5543718BF5B7AF75, ADD INDEX IDX_5543718BF5B7AF75 (address_id)');
    }
}
