<?php

declare(strict_types=1);

// namespace DoctrineMigrations; // For dev

namespace App\Migrations; // For test/prod

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Evol #4468.
 */
final class Version20220715153149 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 5;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 8;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 11;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 13;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 16;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 39;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 43;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 47;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 52;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 56;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 60;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 86;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 138;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 139;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 140;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 141;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 142;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
    }
}
