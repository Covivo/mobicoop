<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416062459 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Adds action 139
        $this->addSql('INSERT INTO `action` (id, name, in_log, in_diary, progression, created_date, updated_date, `position`, `type`) VALUES (140, "eec_subscription_not_ready_to_verify", NULL, NULL, NULL, "2024-04-16 08:00", NULL, 0, NULL)');
        // Adds notification
        $this->addSql('INSERT INTO mobicoop_test_db.notification (id, action_id, medium_id, template_title, active, template_body, created_date, updated_date, user_active_default, user_editable, `position`, alt) VALUES (175, 140, 2, NULL, 1, NULL, "2024-04-16 08:00", NULL, 1, 0, 0, NULL)');
    }

    public function down(Schema $schema): void {}
}
