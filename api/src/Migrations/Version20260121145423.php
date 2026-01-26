<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add gratuity_remind_to_certify_next_day_carpool action and push notification.
 */
final class Version20260121145423 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Create action for next day carpool certification reminder
        $this->addSql("INSERT INTO `action` (id, name, in_log, in_diary, progression, created_date, updated_date, `position`, `type`) VALUES(146, 'gratuity_remind_to_certify_next_day_carpool', NULL, NULL, NULL, '2026-01-21 11:00:00.000', NULL, 0, NULL);");

        // Create push notification linked to the action (medium_id 4 = PUSH)
        $this->addSql("INSERT INTO notification (id, action_id, medium_id, template_title, active, template_body, created_date, updated_date, user_active_default, user_editable, `position`, alt, max_emmitted_per_day) VALUES(180, 146, 4, NULL, 1, NULL, '2026-01-21 11:00:00.000', NULL, 1, 0, 0, 0, 25);");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM notification WHERE id = 180');
        $this->addSql('DELETE FROM `action` WHERE id = 146');
    }
}
