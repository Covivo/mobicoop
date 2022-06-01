<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701122819 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411F9AF0D15');
        $this->addSql('DROP INDEX uniq_a95c6411f9af0d15 ON solidary_matching');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A95C64112137AB3 ON solidary_matching (solidary_matching_linked_id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411F9AF0D15 FOREIGN KEY (solidary_matching_linked_id) REFERENCES solidary_matching (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C64112137AB3');
        $this->addSql('DROP INDEX uniq_a95c64112137ab3 ON solidary_matching');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A95C6411F9AF0D15 ON solidary_matching (solidary_matching_linked_id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C64112137AB3 FOREIGN KEY (solidary_matching_linked_id) REFERENCES solidary_matching (id) ON DELETE CASCADE');
    }
}
