<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate.
 */
final class Version20211223103700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
        INSERT INTO `action` (`id`, `name`, `in_diary`, `progression`, `position`, `type`) VALUES
        (106, 'solidary_external_driver_contact', 1, 50, 0, 2),
        (107, 'solidary_external_carpool', 1, 100, 0, 4);
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
