<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200515095252 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof ADD origin_driver_address_id INT DEFAULT NULL, ADD destination_driver_address_id INT DEFAULT NULL, ADD start_driver_date DATETIME DEFAULT NULL, ADD end_driver_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE876555CE FOREIGN KEY (origin_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEC4EE903F FOREIGN KEY (destination_driver_address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CE876555CE ON carpool_proof (origin_driver_address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_59B969CEC4EE903F ON carpool_proof (destination_driver_address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE876555CE');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEC4EE903F');
        $this->addSql('DROP INDEX UNIQ_59B969CE876555CE ON carpool_proof');
        $this->addSql('DROP INDEX UNIQ_59B969CEC4EE903F ON carpool_proof');
        $this->addSql('ALTER TABLE carpool_proof DROP origin_driver_address_id, DROP destination_driver_address_id, DROP start_driver_date, DROP end_driver_date');
    }
}
