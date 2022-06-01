<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200706102000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Action when there are errors in a Mass analyze
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (74, 'mass_analyze_errors', NULL, NULL, NULL, '2020-07-06 10:00:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '74', '2', NULL, '1', NULL, '2020-07-06 10:00:00', NULL, '1', '0', '0')");

        // Action when the matching of a Mass is over
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (75, 'mass_matched', NULL, NULL, NULL, '2020-07-06 11:30:00', NULL, '0', NULL)");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES (NULL, '75', '2', NULL, '1', NULL, '2020-07-06 11:30:00', NULL, '1', '0', '0')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
