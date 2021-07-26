<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210726071440 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary ADD solidary_child_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4A82E5483 FOREIGN KEY (solidary_child_id) REFERENCES solidary (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1896BCA4A82E5483 ON solidary (solidary_child_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4A82E5483');
        $this->addSql('DROP INDEX UNIQ_1896BCA4A82E5483 ON solidary');
        $this->addSql('ALTER TABLE solidary DROP solidary_child_id');
    }
}
