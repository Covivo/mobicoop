<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Manual migration for actions and notification positions
 * Also add push notifications.
 */
final class Version20191030113300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `notification` SET position=2 WHERE ID=30;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
