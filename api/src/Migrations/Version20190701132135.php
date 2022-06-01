<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190701132135 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sent_by (id INT AUTO_INCREMENT NOT NULL, message_id INT DEFAULT NULL, medium_id INT DEFAULT NULL, sent_date DATETIME NOT NULL, INDEX IDX_C378DCF6537A1329 (message_id), INDEX IDX_C378DCF6E252B6A5 (medium_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medium (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, ask_id INT DEFAULT NULL, message_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, text LONGTEXT NOT NULL, created_date DATETIME NOT NULL, INDEX IDX_B6BD307FA76ED395 (user_id), INDEX IDX_B6BD307FB93F8B63 (ask_id), INDEX IDX_B6BD307F537A1329 (message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipient (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, message_id INT NOT NULL, medium_id INT NOT NULL, status SMALLINT NOT NULL, read_date DATETIME NOT NULL, INDEX IDX_6804FB49A76ED395 (user_id), INDEX IDX_6804FB49537A1329 (message_id), INDEX IDX_6804FB49E252B6A5 (medium_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sent_by ADD CONSTRAINT FK_C378DCF6537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE sent_by ADD CONSTRAINT FK_C378DCF6E252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49E252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sent_by DROP FOREIGN KEY FK_C378DCF6E252B6A5');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49E252B6A5');
        $this->addSql('ALTER TABLE sent_by DROP FOREIGN KEY FK_C378DCF6537A1329');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F537A1329');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49537A1329');
        $this->addSql('DROP TABLE sent_by');
        $this->addSql('DROP TABLE medium');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE recipient');
    }
}
