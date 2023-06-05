<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230605145646 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP FOREIGN KEY FK_894AD28FFBF2A5E5');
        $this->addSql('DROP INDEX UNIQ_894AD28FFBF2A5E5 ON mobconnect__long_distance_journey');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey CHANGE carpool_proof_id carpool_item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD CONSTRAINT FK_894AD28F313229E0 FOREIGN KEY (carpool_item_id) REFERENCES carpool_item (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_894AD28F313229E0 ON mobconnect__long_distance_journey (carpool_item_id)');

        // Data update
        $this->addSql('UPDATE mobconnect__long_distance_journey j SET j.carpool_item_id = ( SELECT ci.id FROM carpool_item ci INNER JOIN ask a ON ci.ask_id = a.id INNER JOIN matching m ON a.matching_id = m.id AND m.common_distance >= 80000 INNER JOIN carpool_proof cp ON a.id = cp.ask_id WHERE cp.id = j.carpool_item_id ) WHERE j.carpool_item_id IS NOT NULL;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP FOREIGN KEY FK_894AD28F313229E0');
        $this->addSql('DROP INDEX UNIQ_894AD28F313229E0 ON mobconnect__long_distance_journey');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey CHANGE carpool_item_id carpool_proof_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD CONSTRAINT FK_894AD28FFBF2A5E5 FOREIGN KEY (carpool_proof_id) REFERENCES carpool_proof (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_894AD28FFBF2A5E5 ON mobconnect__long_distance_journey (carpool_proof_id)');
    }
}
