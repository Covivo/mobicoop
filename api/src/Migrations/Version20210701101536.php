<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rename gamification actions
 */
final class Version20210701101536 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO `action` (`id`, `name`, `in_log`, `in_diary`, `progression`, `created_date`, `updated_date`, `position`, `type`) VALUES (100, 'carpool_done', '1', NULL, NULL, '2021-07-01 10:20:26', NULL, '0', NULL);");
        $this->addSql("UPDATE `gamification_action` SET `name` = 'carpooled_n_km' WHERE `gamification_action`.`id` = 12;");
        $this->addSql("UPDATE `gamification_action` SET `action_id` = 100 WHERE `gamification_action`.`id` = 12;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
