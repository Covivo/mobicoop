<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200325144401 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // we set the notification associated to action 'carpool_proposal_canceled' to not editable by user for the media 'email'
        $this->addSql('UPDATE `notification` SET user_editable=0 WHERE id=24');
        // we remove that notification of all user since she's user editable anymore
        $this->addSql('DELETE FROM `user_notification` WHERE notification_id=24');
    }

    public function down(Schema $schema): void
    {
    }
}
