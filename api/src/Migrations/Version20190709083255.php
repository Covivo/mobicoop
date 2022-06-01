<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190709083255 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE notified ADD proposal_id INT DEFAULT NULL, ADD matching_id INT DEFAULT NULL, ADD ask_id INT DEFAULT NULL, ADD recipient_id INT DEFAULT NULL, ADD created_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id)');
        $this->addSql('CREATE INDEX IDX_D23269D4F4792058 ON notified (proposal_id)');
        $this->addSql('CREATE INDEX IDX_D23269D4B39876B8 ON notified (matching_id)');
        $this->addSql('CREATE INDEX IDX_D23269D4B93F8B63 ON notified (ask_id)');
        $this->addSql('CREATE INDEX IDX_D23269D4E92F8F78 ON notified (recipient_id)');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49E252B6A5');
        $this->addSql('DROP INDEX IDX_6804FB49E252B6A5 ON recipient');
        $this->addSql('ALTER TABLE recipient DROP medium_id');
        $this->addSql('ALTER TABLE notification ADD template_body VARCHAR(255) DEFAULT NULL, CHANGE template template_title VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE notification ADD template VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP template_title, DROP template_body, CHANGE action_id action_id INT DEFAULT NULL, CHANGE medium_id medium_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4F4792058');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4B39876B8');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4B93F8B63');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4E92F8F78');
        $this->addSql('DROP INDEX IDX_D23269D4F4792058 ON notified');
        $this->addSql('DROP INDEX IDX_D23269D4B39876B8 ON notified');
        $this->addSql('DROP INDEX IDX_D23269D4B93F8B63 ON notified');
        $this->addSql('DROP INDEX IDX_D23269D4E92F8F78 ON notified');
        $this->addSql('ALTER TABLE notified DROP proposal_id, DROP matching_id, DROP ask_id, DROP recipient_id, DROP created_date, CHANGE notification_id notification_id INT DEFAULT NULL, CHANGE medium_id medium_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipient ADD medium_id INT DEFAULT NULL, CHANGE message_id message_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49E252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
        $this->addSql('CREATE INDEX IDX_6804FB49E252B6A5 ON recipient (medium_id)');
    }
}
