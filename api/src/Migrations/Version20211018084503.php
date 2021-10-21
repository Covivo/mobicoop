<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018084503 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE relay_point_import DROP FOREIGN KEY FK_ACB71A0D68A482E');
        $this->addSql('ALTER TABLE relay_point_import ADD CONSTRAINT FK_ACB71A0D68A482E FOREIGN KEY (relay_id) REFERENCES relay_point (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE relay_point_import DROP FOREIGN KEY FK_ACB71A0D68A482E');
        $this->addSql('ALTER TABLE relay_point_import ADD CONSTRAINT FK_ACB71A0D68A482E FOREIGN KEY (relay_id) REFERENCES relay_point (id)');
    }
}
