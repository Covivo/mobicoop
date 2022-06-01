<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201004075242 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B818044A959');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81A862FD7E');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B818044A959 FOREIGN KEY (direction_passenger_id) REFERENCES direction (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81A862FD7E FOREIGN KEY (direction_driver_id) REFERENCES direction (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81A862FD7E');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B818044A959');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81A862FD7E FOREIGN KEY (direction_driver_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B818044A959 FOREIGN KEY (direction_passenger_id) REFERENCES direction (id) ON DELETE CASCADE');
    }
}
