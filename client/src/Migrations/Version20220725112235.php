<?php

declare(strict_types=1);

// namespace DoctrineMigrations; // For dev

namespace App\Migrations; // For test/prod

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Evol #4726.
 */
final class Version20220725112235 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 143;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 144;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 145;');
        $this->addSql('UPDATE `notification` SET `alt` = 1 WHERE `id` = 146;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
    }
}
