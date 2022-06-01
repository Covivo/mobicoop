<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200324162000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
        INSERT INTO `action` (`id`, `name`, `in_diary`, `progression`, `position`, `type`) VALUES
        (33, 'solidary_user_create', 1, 0, 0, 0),
        (34, 'solidary_user_accepted', 1, 0, 0, 0),
        (35, 'solidary_user_refused', 1, 0, 0, 0),
        (36, 'solidary_user_update', 1, 0, 0, 0),
        (37, 'solidary_create', 1, 0, 0, 0),
        (38, 'solidary_update', 1, 0, 0, 0),
        (39, 'solidary_update_progress_manually', 1, 0, 0, 0),
        (40, 'solidary_more_infos_sms', 1, 25, 0, 1),
        (41, 'solidary_more_infos_message', 1, 25, 0, 1),
        (42, 'solidary_more_infos_email', 1, 25, 0, 1),
        (43, 'solidary_contact_sms', 1, 50, 0, 2),
        (44, 'solidary_contact_message', 1, 50, 0, 2),
        (45, 'solidary_contact_email', 1, 50, 0, 2),
        (46, 'solidary_reminder_sms', 1, 50, 0, 2),
        (47, 'solidary_reminder_message', 1, 50, 0, 2),
        (48, 'solidary_reminder_email', 1, 50, 0, 2),
        (49, 'solidary_link', 1, 50, 0, 2),
        (50, 'solidary_carpool_refusal', 1, 50, 0, 2),
        (51, 'solidary_carpool_ongoing', 1, 75, 0, 3),
        (52, 'solidary_follow_up_ask', 1, 75, 0, 3),
        (53, 'solidary_follow_up_reminder', 1, 75, 0, 3),
        (54, 'solidary_witness_ask', 1, 75, 0, 3),
        (55, 'solidary_witness_reminder', 1, 75, 0, 3),
        (56, 'solidary_punctual_carpool', 1, 100, 0, 4),
        (57, 'solidary_regular_carpool', 1, 100, 0, 4),
        (58, 'solidary_other_transport_solutions', 1, 100, 0, 4),
        (59, 'solidary_ask_drop', 1, 100, 0, 4),
        (60, 'solidary_too_late', 1, 100, 0, 4),
        (61, 'solidary_closed_association', 1, 100, 0, 4),
        (62, 'solidary_no_solution', 1, 100, 0, 4),
        (63, 'solidary_no_available_carpooler', 1, 100, 0, 4),
        (64, 'solidary_carpool_no_response', 1, 100, 0, 4),
        (65, 'solidary_off_topic', 1, 100, 0, 4);
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
