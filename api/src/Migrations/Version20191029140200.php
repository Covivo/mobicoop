<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Manual migration for unused actions and notifications.
 */
final class Version20191029140200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM `notified` WHERE `notification_id` >= 23 and `notification_id` <= 82');
        $this->addSql('DELETE FROM `notification` WHERE `id` >= 23 and `id` <= 82');
        $this->addSql('DELETE FROM `action` WHERE `id` >= 12 and `id` <= 31');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
