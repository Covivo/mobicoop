<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Login delegate
 */
final class Version20210602143355 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Add the interoperability rights for Image resource

        $this->addSql("INSERT INTO `language` (`id`, `code`) VALUES ('1', 'fr');");
        $this->addSql("INSERT INTO `language` (`id`, `code`) VALUES ('2', 'en');");
        $this->addSql("INSERT INTO `language` (`id`, `code`) VALUES ('3', 'eu');");
        $this->addSql("INSERT INTO `language` (`id`, `code`) VALUES ('4', 'it');");
        $this->addSql("INSERT INTO `language` (`id`, `code`) VALUES ('5', 'de');");
        $this->addSql("INSERT INTO `language` (`id`, `code`) VALUES ('6', 'es');");
        $this->addSql("INSERT INTO `language` (`id`, `code`) VALUES ('7', 'nl');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
