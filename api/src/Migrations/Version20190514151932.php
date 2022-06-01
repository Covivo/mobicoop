<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190514151932 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE relay_point (id INT AUTO_INCREMENT NOT NULL, address_id INT NOT NULL, user_id INT NOT NULL, community_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, private TINYINT(1) DEFAULT NULL, description VARCHAR(255) NOT NULL, full_description LONGTEXT NOT NULL, status SMALLINT NOT NULL, places SMALLINT DEFAULT NULL, places_disabled SMALLINT DEFAULT NULL, free TINYINT(1) DEFAULT NULL, secured TINYINT(1) DEFAULT NULL, official TINYINT(1) DEFAULT NULL, suggested TINYINT(1) DEFAULT NULL, permalink VARCHAR(255) DEFAULT NULL, created_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_A9BE6C9CF5B7AF75 (address_id), UNIQUE INDEX UNIQ_A9BE6C9CA76ED395 (user_id), INDEX IDX_A9BE6C9CFDA7B0BF (community_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relay_point_relay_point_type (relay_point_id INT NOT NULL, relay_point_type_id INT NOT NULL, INDEX IDX_848D417877D93E2D (relay_point_id), INDEX IDX_848D4178D8CA6523 (relay_point_type_id), PRIMARY KEY(relay_point_id, relay_point_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE relay_point_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE relay_point_relay_point_type ADD CONSTRAINT FK_848D417877D93E2D FOREIGN KEY (relay_point_id) REFERENCES relay_point (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE relay_point_relay_point_type ADD CONSTRAINT FK_848D4178D8CA6523 FOREIGN KEY (relay_point_type_id) REFERENCES relay_point_type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE relay_point_relay_point_type DROP FOREIGN KEY FK_848D417877D93E2D');
        $this->addSql('ALTER TABLE relay_point_relay_point_type DROP FOREIGN KEY FK_848D4178D8CA6523');
        $this->addSql('DROP TABLE relay_point');
        $this->addSql('DROP TABLE relay_point_relay_point_type');
        $this->addSql('DROP TABLE relay_point_type');
    }
}
