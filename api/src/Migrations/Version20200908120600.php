<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200908120600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (19, "COVENTION", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (20, "HOW_IT_WORKS", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (21, "CARPOOL", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (22, "CARPOOLING", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (23, "CARPOOLING_AREAS", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (24, "PDM", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (25, "TALK_ABOUT_US", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (26, "FEES", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (27, "MEDIAS", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (28, "USEFUL_LINKS", 1)');
        $this->addSql('INSERT INTO article (`id`, `title`, `status`) VALUES (29, "MOBILE_APP", 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
