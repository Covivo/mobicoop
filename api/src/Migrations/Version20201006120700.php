<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Remove direction for matching's criteria.
 */
final class Version20201006120700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
            CREATE TEMPORARY TABLE direction_remove (
            id int NOT NULL,
            PRIMARY KEY(id));
        ');
        $this->addSql('
            INSERT INTO direction_remove (id)
            (SELECT direction_driver_id FROM criteria INNER JOIN matching ON matching.criteria_id = criteria.id);
        ');

        $this->addSql('DELETE FROM direction WHERE id in (SELECT id FROM direction_remove);');

        $this->addSql('
            DROP TABLE direction_remove;
        ');

        $this->addSql('OPTIMIZE TABLE direction;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
