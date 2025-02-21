<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214113223 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE notified ADD solidary_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D23269D4E92CE751 ON notified (solidary_id)');

        // FIXTURES
        $this->addSql("INSERT INTO `action` (id, name, in_log, in_diary, progression, created_date, updated_date, `position`, `type`) VALUES(143, 'solidary_volunteer_matching_success', NULL, NULL, NULL, '2025-02-14 08:00:00.000', NULL, 0, NULL);");
        $this->addSql("INSERT INTO notification (id, action_id, medium_id, template_title, active, template_body, created_date, updated_date, user_active_default, user_editable, `position`, alt, max_emmitted_per_day) VALUES(178, 143, 3, NULL, 1, NULL, '2025-02-14 08:00:00.000', NULL, 1, 0, 0, 0, 25);");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4E92CE751');
        $this->addSql('DROP INDEX IDX_D23269D4E92CE751 ON notified');
        $this->addSql('ALTER TABLE notified DROP solidary_id');
    }
}
