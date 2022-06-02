<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220601084613 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image ADD structure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C53D045F2534008B ON image (structure_id)');
        $this->addSql('ALTER TABLE structure ADD signature VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F2534008B');
        $this->addSql('DROP INDEX IDX_C53D045F2534008B ON image');
        $this->addSql('ALTER TABLE image DROP structure_id');
        $this->addSql('ALTER TABLE structure DROP signature');
    }
}
