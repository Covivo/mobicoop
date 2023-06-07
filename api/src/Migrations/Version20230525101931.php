<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230525101931 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (131, 'external_connection_confirmed', NULL, NULL, NULL, '2023-05-25 10:19:31', NULL, 0, NULL);");

        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (161, 131, 2, NULL, 1, NULL, '2023-05-25 10:19:31', NULL, 1, 0, 0);");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
