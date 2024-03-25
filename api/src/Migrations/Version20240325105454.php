<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240325105454 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE waypoint DROP INDEX IDX_B3DC5881F5B7AF75, ADD UNIQUE INDEX UNIQ_B3DC5881F5B7AF75 (address_id)');
        $this->addSql('ALTER TABLE mass DROP INDEX IDX_6C035B66FDA7B0BF, ADD UNIQUE INDEX UNIQ_6C035B66FDA7B0BF (community_id)');
        $this->addSql('ALTER TABLE mass_person DROP INDEX IDX_75908575A76ED395, ADD UNIQUE INDEX UNIQ_75908575A76ED395 (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass DROP INDEX UNIQ_6C035B66FDA7B0BF, ADD INDEX IDX_6C035B66FDA7B0BF (community_id)');
        $this->addSql('ALTER TABLE mass_person DROP INDEX UNIQ_75908575A76ED395, ADD INDEX IDX_75908575A76ED395 (user_id)');
        $this->addSql('ALTER TABLE waypoint DROP INDEX UNIQ_B3DC5881F5B7AF75, ADD INDEX IDX_B3DC5881F5B7AF75 (address_id)');
    }
}
