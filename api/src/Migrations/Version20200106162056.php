<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Manual migration for actions and notification positions
 * Also add push notifications
 */
final class Version20200106162056 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=33;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=34;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=35;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=36;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=37;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=38;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=39;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=40;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=41;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=42;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=43;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=44;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=45;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=46;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=47;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=48;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=49;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=50;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=51;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=52;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=53;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=54;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=55;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=56;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=57;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=58;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=59;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=60;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=61;');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE ID=62;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
