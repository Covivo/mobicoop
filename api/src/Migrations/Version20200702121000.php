<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200702121000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
        INSERT INTO `action` (`id`, `name`, `in_diary`, `progression`, `position`, `type`) VALUES
        (71, 'solidary_more_infos_call', 1, 25, 0, 1),
        (72, 'solidary_contact_call', 1, 50, 0, 2),
        (73, 'solidary_reminder_call', 1, 50, 0, 2);
        ");
        $this->addSql("
        UPDATE `action` SET `type` = NULL WHERE `action`.`id` = 42;
        ");
        $this->addSql("
        UPDATE `action` SET `type` = NULL WHERE `action`.`id` = 45;
        ");
        $this->addSql("
        UPDATE `action` SET `type` = NULL WHERE `action`.`id` = 48;
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
