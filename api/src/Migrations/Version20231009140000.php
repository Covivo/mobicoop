<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231009140000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO
                `sso_account` (
                    user_id,
                    sso_id,
                    sso_provider,
                    created_by_sso,
                    created_date
                )
            SELECT
                id,
                sso_id,
                sso_provider,
                IF (created_by_sso is null, 0, created_by_sso) as created_by_sso,
                IF (created_sso_date is null, NOW(), created_sso_date) as created_date
            FROM
                `user`
            where
                sso_id is not null');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sso_account');
    }
}
