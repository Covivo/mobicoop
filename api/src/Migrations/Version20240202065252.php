<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240202065252 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `position`) VALUES (137, \'certify_pick_up_before_start\', 0);');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `position`) VALUES (138, \'certify_drop_off_before_end\', 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `user_active_default`, `user_editable`, `active`, `position`) VALUES (172, 137, 4, null, 1, 0, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `user_active_default`, `user_editable`, `active`, `position`) VALUES (173, 138, 4, null, 1, 0, 1, 0);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
