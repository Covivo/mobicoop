<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330072655 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD commitment_proof_timestamp_token LONGTEXT DEFAULT NULL COMMENT \'The long distance ECC commitment proof timestamp\', ADD commitment_proof_timestamp_signing_time DATETIME DEFAULT NULL COMMENT \'The long distance EEC commitment proof timestamp signing time\', ADD honor_certificate_proof_timestamp_token LONGTEXT DEFAULT NULL COMMENT \'The long distance EEC honor certificate proof timestamp\', ADD honor_certificate_proof_timestamp_signing_time DATETIME DEFAULT NULL COMMENT \'The long distance EEC honor certificate proof timestamp signing time\', ADD incentive_proof_timestamp_token LONGTEXT DEFAULT NULL COMMENT \'The long distance EEC incentive proof timestamp\', ADD incentive_proof_timestamp_signing_time DATETIME DEFAULT NULL COMMENT \'The long distance EEC incentive proof timestamp signing time\', DROP IF EXISTS commitment_proof_timestamp, DROP IF EXISTS honor_certificate_proof_timestamp, DROP IF EXISTS incentive_proof_timestamp');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD commitment_proof_timestamp_token LONGTEXT DEFAULT NULL COMMENT \'The long distance ECC commitment proof timestamp\', ADD commitment_proof_timestamp_signing_time DATETIME DEFAULT NULL COMMENT \'The long distance EEC commitment proof timestamp signing time\', ADD honor_certificate_proof_timestamp_token LONGTEXT DEFAULT NULL COMMENT \'The long distance EEC honor certificate proof timestamp\', ADD honor_certificate_proof_timestamp_signing_time DATETIME DEFAULT NULL COMMENT \'The long distance EEC honor certificate proof timestamp signing time\', ADD incentive_proof_timestamp_token LONGTEXT DEFAULT NULL COMMENT \'The long distance EEC incentive proof timestamp\', ADD incentive_proof_timestamp_signing_time DATETIME DEFAULT NULL COMMENT \'The long distance EEC incentive proof timestamp signing time\', DROP IF EXISTS commitment_proof_timestamp, DROP IF EXISTS honor_certificate_proof_timestamp, DROP IF EXISTS incentive_proof_timestamp');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD commitment_proof_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'The long distance ECC commitment proof timestamp\', ADD honor_certificate_proof_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'The long distance EEC honor certificate proof timestamp\', ADD incentive_proof_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'The long distance EEC incentive proof timestamp\', DROP commitment_proof_timestamp_token, DROP commitment_proof_timestamp_signing_time, DROP honor_certificate_proof_timestamp_token, DROP honor_certificate_proof_timestamp_signing_time, DROP incentive_proof_timestamp_token, DROP incentive_proof_timestamp_signing_time');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD commitment_proof_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'The long distance ECC commitment proof timestamp\', ADD honor_certificate_proof_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'The short distance EEC honor certificate proof timestamp\', ADD incentive_proof_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'The short distance EEC incentive proof timestamp\', DROP commitment_proof_timestamp_token, DROP commitment_proof_timestamp_signing_time, DROP honor_certificate_proof_timestamp_token, DROP honor_certificate_proof_timestamp_signing_time, DROP incentive_proof_timestamp_token, DROP incentive_proof_timestamp_signing_time');
    }
}
