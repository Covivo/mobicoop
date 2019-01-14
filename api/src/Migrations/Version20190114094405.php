<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190114094405 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) NOT NULL, CHANGE multi_transport_mode multi_transport_mode TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ptjourney DROP change_number');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ptjourney ADD change_number INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT NULL, CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT NULL');
    }
}
