<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate.
 */
final class Version20210726144300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `article` (`id`, `title`, `status`, `created_date`, `updated_date`, `i_frame`) VALUES ('34', 'I_AM_PRIVATE_PERSON', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `article` (`id`, `title`, `status`, `created_date`, `updated_date`, `i_frame`) VALUES ('35', 'I_AM_SOCIETY', '1', NULL, NULL, NULL);");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
