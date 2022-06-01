<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191113131905 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        // insert actions
        $this->addSql('INSERT INTO `action` (`id`, `name`, `position` ) VALUES (13, \'user_generate_phone_token_asked\', 0);');
        // insert notifications
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `user_active_default`, `user_editable`, `position` ) VALUES (33, 13, 3, 1, 1, 0, 0);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DELETE FROM `action` WHERE `id` = 13;');
        $this->addSql('DELETE FROM `notification` WHERE `id` = 33;');
    }
}
