<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210722120220 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary ADD solidary_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1896BCA4E92CE751 ON solidary (solidary_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4E92CE751');
        $this->addSql('DROP INDEX UNIQ_1896BCA4E92CE751 ON solidary');
        $this->addSql('ALTER TABLE solidary DROP solidary_id');
    }
}
