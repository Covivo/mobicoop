<?php

declare(strict_types=1);

// namespace DoctrineMigrations; // For dev

namespace App\Migrations; // For test/prod

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * New emails.
 */
final class Version20230216171100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
        $this->addSql("UPDATE `notification` SET `alt` = '1' WHERE `notification`.`id` = 152;");
        $this->addSql("UPDATE `notification` SET `alt` = '1' WHERE `notification`.`id` = 153;");
        $this->addSql("UPDATE `notification` SET `alt` = '1' WHERE `notification`.`id` = 154;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.)');
    }
}
