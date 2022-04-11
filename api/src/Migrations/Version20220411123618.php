<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220411123618 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_import DROP FOREIGN KEY FK_F81CD520A76ED395');
        $this->addSql('ALTER TABLE user_import ADD CONSTRAINT FK_F81CD520A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE community_import DROP FOREIGN KEY FK_EC843BEEFDA7B0BF');
        $this->addSql('ALTER TABLE community_import ADD CONSTRAINT FK_EC843BEEFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_import DROP FOREIGN KEY FK_AFD7B94F71F7E88B');
        $this->addSql('ALTER TABLE event_import ADD CONSTRAINT FK_AFD7B94F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE community_import DROP FOREIGN KEY FK_EC843BEEFDA7B0BF');
        $this->addSql('ALTER TABLE community_import ADD CONSTRAINT FK_EC843BEEFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE event_import DROP FOREIGN KEY FK_AFD7B94F71F7E88B');
        $this->addSql('ALTER TABLE event_import ADD CONSTRAINT FK_AFD7B94F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE user_import DROP FOREIGN KEY FK_F81CD520A76ED395');
        $this->addSql('ALTER TABLE user_import ADD CONSTRAINT FK_F81CD520A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
