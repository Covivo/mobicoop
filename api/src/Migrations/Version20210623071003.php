<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210623071003 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_ask ADD solidary_ask_linked_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF656657410 FOREIGN KEY (solidary_ask_linked_id) REFERENCES solidary_ask (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F77CFF656657410 ON solidary_ask (solidary_ask_linked_id)');
        $this->addSql('ALTER TABLE solidary_matching ADD solidary_matching_linked_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411F9AF0D15 FOREIGN KEY (solidary_matching_linked_id) REFERENCES solidary_matching (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A95C6411F9AF0D15 ON solidary_matching (solidary_matching_linked_id)');
        $this->addSql('ALTER TABLE solidary_solution ADD solidary_solution_linked_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF432BBAF973 FOREIGN KEY (solidary_solution_linked_id) REFERENCES solidary_solution (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EA7FBF432BBAF973 ON solidary_solution (solidary_solution_linked_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF656657410');
        $this->addSql('DROP INDEX UNIQ_1F77CFF656657410 ON solidary_ask');
        $this->addSql('ALTER TABLE solidary_ask DROP solidary_ask_linked_id');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411F9AF0D15');
        $this->addSql('DROP INDEX UNIQ_A95C6411F9AF0D15 ON solidary_matching');
        $this->addSql('ALTER TABLE solidary_matching DROP solidary_matching_linked_id');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF432BBAF973');
        $this->addSql('DROP INDEX UNIQ_EA7FBF432BBAF973 ON solidary_solution');
        $this->addSql('ALTER TABLE solidary_solution DROP solidary_solution_linked_id');
    }
}
