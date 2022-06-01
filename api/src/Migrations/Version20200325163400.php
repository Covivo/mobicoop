<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200325163400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        // we pass user_editable to false for all notifications with internal message as media since it's for now not activated
        $this->addSql('UPDATE `notification` SET user_editable=0 WHERE medium_id=1');
        // we remove all user_notification with media=internal_message
        $this->addSql('DELETE FROM `user_notification` WHERE `notification_id` IN (SELECT `id` FROM `notification` WHERE `medium_id` = 1)');
    }

    public function down(Schema $schema): void
    {
    }
}
