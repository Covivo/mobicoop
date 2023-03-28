<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230309113000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE mobconnect__short_distance_subscription msds INNER JOIN mobconnect__short_distance_journey msdj ON msdj.subscription_id = msds.id AND msdj.http_request_status = 204 SET msds.commitment_proof_date = msdj.created_at WHERE msds.status IS NULL AND msds.commitment_proof_date IS NULL');
        $this->addSql('UPDATE mobconnect__long_distance_subscription mlds INNER JOIN mobconnect__long_distance_journey mldj ON mldj.subscription_id = mlds.id AND mldj.http_request_status = 204 SET mlds.commitment_proof_date = mldj.created_at WHERE mlds.status IS NULL AND mlds.commitment_proof_date IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
