<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate
 */
final class Version20201211131527 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (89, 'user_send_validation_email', NULL, NULL, NULL, '2020-12-11 13:15:27', NULL, 0, NULL)");
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position`) VALUES (128, 89, 2, 1, 1, 0, 0);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
