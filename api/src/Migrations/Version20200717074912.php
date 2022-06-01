<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200717074912 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE relay_point_relay_point_type');
        $this->addSql('ALTER TABLE relay_point ADD relay_point_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CD8CA6523 FOREIGN KEY (relay_point_type_id) REFERENCES relay_point_type (id)');
        $this->addSql('CREATE INDEX IDX_A9BE6C9CD8CA6523 ON relay_point (relay_point_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE relay_point_relay_point_type (relay_point_id INT NOT NULL, relay_point_type_id INT NOT NULL, INDEX IDX_848D417877D93E2D (relay_point_id), INDEX IDX_848D4178D8CA6523 (relay_point_type_id), PRIMARY KEY(relay_point_id, relay_point_type_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE relay_point_relay_point_type ADD CONSTRAINT FK_848D417877D93E2D FOREIGN KEY (relay_point_id) REFERENCES relay_point (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE relay_point_relay_point_type ADD CONSTRAINT FK_848D4178D8CA6523 FOREIGN KEY (relay_point_type_id) REFERENCES relay_point_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9CD8CA6523');
        $this->addSql('DROP INDEX IDX_A9BE6C9CD8CA6523 ON relay_point');
        $this->addSql('ALTER TABLE relay_point DROP relay_point_type_id');
    }
}
