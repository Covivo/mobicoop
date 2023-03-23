<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230306071924 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD bonus_status SMALLINT DEFAULT 0 NOT NULL COMMENT \'Bonus Status of the EEC form\', ADD commitment_proof_date DATETIME DEFAULT NULL COMMENT \'The long distance ECC commitment proof date\', ADD commitment_proof_timestamp VARCHAR(255) DEFAULT NULL COMMENT \'The long distance ECC commitment proof timestamp\', DROP initial_timestamp, DROP last_timestamp');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP INDEX UNIQ_894AD28F1212FDF6, ADD INDEX IDX_894AD28F1212FDF6 (carpool_payment_id)');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD initial_proposal_id INT DEFAULT NULL, DROP bonus_status, DROP verification_status, DROP rank');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD CONSTRAINT FK_894AD28FE8E333A5 FOREIGN KEY (initial_proposal_id) REFERENCES proposal (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_894AD28FE8E333A5 ON mobconnect__long_distance_journey (initial_proposal_id)');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey DROP bonus_status, DROP verification_status, DROP rank');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD bonus_status SMALLINT DEFAULT 0 NOT NULL COMMENT \'Bonus Status of the EEC form\', ADD commitment_proof_date DATETIME DEFAULT NULL COMMENT \'The long distance ECC commitment proof date\', ADD commitment_proof_timestamp VARCHAR(255) DEFAULT NULL COMMENT \'The long distance ECC commitment proof timestamp\', DROP initial_timestamp, DROP last_timestamp');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP INDEX IDX_894AD28F1212FDF6, ADD UNIQUE INDEX UNIQ_894AD28F1212FDF6 (carpool_payment_id)');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP FOREIGN KEY FK_894AD28FE8E333A5');
        $this->addSql('DROP INDEX UNIQ_894AD28FE8E333A5 ON mobconnect__long_distance_journey');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD bonus_status SMALLINT DEFAULT 0 NOT NULL COMMENT \'Bonus Status of the EEC form\', ADD verification_status INT DEFAULT 0 COMMENT \'Status of verification\', ADD rank INT DEFAULT NULL COMMENT \'Rank of the journey for the user\', DROP initial_proposal_id');
        $this->addSql('ALTER TABLE mobconnect__long_distance_subscription ADD initial_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD last_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP bonus_status, DROP commitment_proof_date, DROP commitment_proof_timestamp');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey ADD bonus_status SMALLINT DEFAULT 0 NOT NULL COMMENT \'Bonus Status of the EEC form\', ADD verification_status INT DEFAULT 0 COMMENT \'Status of verification\', ADD rank INT DEFAULT NULL COMMENT \'Rank of the journey for the user\'');
        $this->addSql('ALTER TABLE mobconnect__short_distance_subscription ADD initial_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD last_timestamp VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP bonus_status, DROP commitment_proof_date, DROP commitment_proof_timestamp');
    }
}
