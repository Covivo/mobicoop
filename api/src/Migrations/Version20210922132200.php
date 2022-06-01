<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210922132200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sequence_item ADD value INT DEFAULT NULL');
        $this->addSql('UPDATE `gamification_action` SET `action_id` = 100 WHERE `gamification_action`.`id` = 13;');
        $this->addSql('UPDATE `sequence_item` SET `min_count` = NULL WHERE `sequence_item`.`id` = 12;');
        $this->addSql('UPDATE `sequence_item` SET `min_count` = NULL WHERE `sequence_item`.`id` = 13;');
        $this->addSql('UPDATE `sequence_item` SET `value` = 500 WHERE `sequence_item`.`id` = 12;');
        $this->addSql('UPDATE `sequence_item` SET `value` = 100 WHERE `sequence_item`.`id` = 13;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sequence_item DROP value');
    }
}
