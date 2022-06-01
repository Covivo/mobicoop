<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190612121507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_matching DROP FOREIGN KEY FK_9B3B75EBAF73D997');
        $this->addSql('DROP INDEX IDX_9B3B75EBAF73D997 ON mass_matching');
        $this->addSql('ALTER TABLE mass_matching ADD distance INT NOT NULL, ADD duration INT NOT NULL, DROP direction_id');
        $this->addSql('ALTER TABLE mass_person DROP FOREIGN KEY FK_75908575AF73D997');
        $this->addSql('DROP INDEX IDX_75908575AF73D997 ON mass_person');
        $this->addSql('ALTER TABLE mass_person ADD distance INT NOT NULL, ADD duration INT NOT NULL, DROP direction_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_matching ADD direction_id INT DEFAULT NULL, DROP distance, DROP duration');
        $this->addSql('ALTER TABLE mass_matching ADD CONSTRAINT FK_9B3B75EBAF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id)');
        $this->addSql('CREATE INDEX IDX_9B3B75EBAF73D997 ON mass_matching (direction_id)');
        $this->addSql('ALTER TABLE mass_person ADD direction_id INT DEFAULT NULL, DROP distance, DROP duration');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575AF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id)');
        $this->addSql('CREATE INDEX IDX_75908575AF73D997 ON mass_person (direction_id)');
    }
}
