<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210614123538 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE translate DROP FOREIGN KEY FK_4A106377953C1C61');
        $this->addSql('DROP TABLE source');
        $this->addSql('DROP INDEX IDX_4A106377953C1C61 ON translate');
        $this->addSql('ALTER TABLE translate ADD domain VARCHAR(45) NOT NULL, ADD id_entity VARCHAR(45) NOT NULL, ADD property VARCHAR(255) NOT NULL, DROP source_id, CHANGE text text LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE source (id INT AUTO_INCREMENT NOT NULL, domain VARCHAR(45) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, property VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE translate ADD source_id INT NOT NULL, DROP domain, DROP id_entity, DROP property, CHANGE text text VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE translate ADD CONSTRAINT FK_4A106377953C1C61 FOREIGN KEY (source_id) REFERENCES source (id)');
        $this->addSql('CREATE INDEX IDX_4A106377953C1C61 ON translate (source_id)');
    }
}
