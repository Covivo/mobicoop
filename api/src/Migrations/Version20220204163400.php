<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220204163400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `gamification_action` SET `title` = '{minCount} annonces publiées' WHERE `gamification_action`.`id` = 6;");
        $this->addSql("UPDATE `gamification_action` SET `title` = '{value} km de covoiturages acceptés' WHERE `gamification_action`.`id` = 12;");
        $this->addSql("UPDATE `gamification_action` SET `title` = '{value} kg de CO² économisés' WHERE `gamification_action`.`id` = 13;");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
