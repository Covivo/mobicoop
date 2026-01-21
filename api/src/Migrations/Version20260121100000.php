<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add gratuity_send_carpool_certification_info action and push notification.
 */
final class Version20260121100000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Create action for carpool certification info notification
        $this->addSql("INSERT INTO `action` (id, name, in_log, in_diary, progression, created_date, updated_date, `position`, `type`) VALUES(145, 'gratuity_send_carpool_certification_info', NULL, NULL, NULL, '2026-01-21 10:00:00.000', NULL, 0, NULL);");

        // Create push notification linked to the action (medium_id 4 = PUSH)
        $this->addSql("INSERT INTO notification (id, action_id, medium_id, template_title, active, template_body, created_date, updated_date, user_active_default, user_editable, `position`, alt, max_emmitted_per_day) VALUES(179, 145, 4, NULL, 1, NULL, '2026-01-21 10:00:00.000', NULL, 1, 0, 0, 0, 25);");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM notification WHERE id = 179');
        $this->addSql('DELETE FROM `action` WHERE id = 145');
    }
}
