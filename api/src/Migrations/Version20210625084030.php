<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210625084030 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reward_step ADD user_id INT DEFAULT NULL, DROP sequence_item, CHANGE user sequence_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reward_step ADD CONSTRAINT FK_42A86EA726659B86 FOREIGN KEY (sequence_item_id) REFERENCES sequence_item (id)');
        $this->addSql('ALTER TABLE reward_step ADD CONSTRAINT FK_42A86EA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_42A86EA726659B86 ON reward_step (sequence_item_id)');
        $this->addSql('CREATE INDEX IDX_42A86EA7A76ED395 ON reward_step (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reward_step DROP FOREIGN KEY FK_42A86EA726659B86');
        $this->addSql('ALTER TABLE reward_step DROP FOREIGN KEY FK_42A86EA7A76ED395');
        $this->addSql('DROP INDEX IDX_42A86EA726659B86 ON reward_step');
        $this->addSql('DROP INDEX IDX_42A86EA7A76ED395 ON reward_step');
        $this->addSql('ALTER TABLE reward_step ADD sequence_item SMALLINT NOT NULL, ADD user INT DEFAULT NULL, DROP sequence_item_id, DROP user_id');
    }
}
