<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Custom Migration to fix falsy OneToOne link between Criteria and Direction !
 */
final class Version20200422143100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `action` (`id`, `name`, `position`) VALUES (68, \'mass_migrate_user_migrated\', 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `user_active_default`, `user_editable`, `active`, `position`) VALUES (91, 68, 2, null, 1, 0, 1, 0);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
