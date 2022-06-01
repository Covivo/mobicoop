<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210209131508 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proposal ADD app_delegate_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472D3EE9239 FOREIGN KEY (app_delegate_id) REFERENCES app (id)');
        $this->addSql('CREATE INDEX IDX_BFE59472D3EE9239 ON proposal (app_delegate_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472D3EE9239');
        $this->addSql('DROP INDEX IDX_BFE59472D3EE9239 ON proposal');
        $this->addSql('ALTER TABLE proposal DROP app_delegate_id');
    }
}
