<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200225163908 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD8EFAB6B1');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD8EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD8EFAB6B1');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD8EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES structure_proof (id)');
    }
}
