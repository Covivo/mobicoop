<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200515093732 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE need DROP FOREIGN KEY FK_E6F46C44E92CE751');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C44E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE need DROP FOREIGN KEY FK_E6F46C44E92CE751');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C44E92CE751 FOREIGN KEY (solidary_id) REFERENCES structure_proof (id)');
    }
}
