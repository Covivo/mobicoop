<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Manual migration for actions and notification positions
 * Also add push notifications
 */
final class Version20191029144400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=4;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=5;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=6;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=7;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=8;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=9;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=10;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=11;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=12;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=13;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=14;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=15;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=16;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=17;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=18;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=19;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=1 WHERE ID=20;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
