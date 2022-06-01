<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190702123833 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE notified (id INT AUTO_INCREMENT NOT NULL, notification_id INT NOT NULL, medium_id INT NOT NULL, user_id INT NOT NULL, status SMALLINT NOT NULL, sent_date DATETIME NOT NULL, received_date DATETIME NOT NULL, read_date DATETIME NOT NULL, INDEX IDX_D23269D4EF1A9D84 (notification_id), INDEX IDX_D23269D4E252B6A5 (medium_id), INDEX IDX_D23269D4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE action (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, uname VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, action_id INT NOT NULL, medium_id INT NOT NULL, template VARCHAR(255) NOT NULL, active TINYINT(1) DEFAULT NULL, active_default TINYINT(1) DEFAULT NULL, INDEX IDX_BF5476CA9D32F035 (action_id), INDEX IDX_BF5476CAE252B6A5 (medium_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4E252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9D32F035');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4EF1A9D84');
        $this->addSql('DROP TABLE notified');
        $this->addSql('DROP TABLE action');
        $this->addSql('DROP TABLE notification');
    }
}
