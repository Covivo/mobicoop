<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203154500 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO `action` (id, name, `position`, `type`) VALUES (141, "hitchhicker_incomplete_registration_first_relaunch", 0, NULL)');
        $this->addSql('INSERT INTO `action` (id, name, `position`, `type`) VALUES (142, "hitchhicker_incomplete_registration_second_relaunch", 0, NULL)');

        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `user_active_default`, `user_editable`, `active`, `position`, `alt`, `max_emmitted_per_day`) VALUES (176, 141, 2, null, 1, 0, 1, 0, 1, 25);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `user_active_default`, `user_editable`, `active`, `position`, `alt`, `max_emmitted_per_day`) VALUES (177, 142, 2, null, 1, 0, 1, 0, 1, 25);');
    }

    public function down(Schema $schema): void {}
}
