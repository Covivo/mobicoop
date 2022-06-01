<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200320074704 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4815BD757');
        $this->addSql('DROP INDEX IDX_1896BCA4815BD757 ON solidary');
        $this->addSql('ALTER TABLE solidary CHANGE solidary_user_id solidary_user_structure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4E51EA2A6 FOREIGN KEY (solidary_user_structure_id) REFERENCES solidary_user_structure (id)');
        $this->addSql('CREATE INDEX IDX_1896BCA4E51EA2A6 ON solidary (solidary_user_structure_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4E51EA2A6');
        $this->addSql('DROP INDEX IDX_1896BCA4E51EA2A6 ON solidary');
        $this->addSql('ALTER TABLE solidary CHANGE solidary_user_structure_id solidary_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('CREATE INDEX IDX_1896BCA4815BD757 ON solidary (solidary_user_id)');
    }
}
