<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200319153717 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F5F5B7AF75');
        $this->addSql('DROP INDEX UNIQ_462CE4F5F5B7AF75 ON position');
        $this->addSql('ALTER TABLE position CHANGE address_id waypoint_id INT NOT NULL');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F57BB1FD97 FOREIGN KEY (waypoint_id) REFERENCES waypoint (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_462CE4F57BB1FD97 ON position (waypoint_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F57BB1FD97');
        $this->addSql('DROP INDEX UNIQ_462CE4F57BB1FD97 ON position');
        $this->addSql('ALTER TABLE position CHANGE waypoint_id address_id INT NOT NULL');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_462CE4F5F5B7AF75 ON position (address_id)');
    }
}
