<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200407143300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE id=1');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE id=2');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE id=3');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE id=83');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE id=84');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE id=85');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE id=86');
        $this->addSql('UPDATE `notification` SET user_active_default=1, user_editable=0 WHERE id=87');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
