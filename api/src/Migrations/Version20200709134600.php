<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200709134600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5642B8210');
        $this->addSql('DROP INDEX IDX_8F3F68C5642B8210 ON log');
        $this->addSql('ALTER TABLE log CHANGE admin_id user_delegate_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C523107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C523107D10 ON log (user_delegate_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C523107D10');
        $this->addSql('DROP INDEX IDX_8F3F68C523107D10 ON log');
        $this->addSql('ALTER TABLE log CHANGE user_delegate_id admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5642B8210 ON log (admin_id)');
    }
}
