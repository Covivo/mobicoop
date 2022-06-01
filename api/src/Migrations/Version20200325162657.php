<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200325162657 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2642B8210');
        $this->addSql('DROP INDEX IDX_917BEDE2642B8210 ON diary');
        $this->addSql('ALTER TABLE diary CHANGE admin_id author_id INT NOT NULL');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_917BEDE2F675F31B ON diary (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2F675F31B');
        $this->addSql('DROP INDEX IDX_917BEDE2F675F31B ON diary');
        $this->addSql('ALTER TABLE diary CHANGE author_id admin_id INT NOT NULL');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_917BEDE2642B8210 ON diary (admin_id)');
    }
}
