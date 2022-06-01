<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190514153722 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image ADD relay_point_id INT DEFAULT NULL, ADD relay_point_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F77D93E2D FOREIGN KEY (relay_point_id) REFERENCES relay_point (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FD8CA6523 FOREIGN KEY (relay_point_type_id) REFERENCES relay_point_type (id)');
        $this->addSql('CREATE INDEX IDX_C53D045F77D93E2D ON image (relay_point_id)');
        $this->addSql('CREATE INDEX IDX_C53D045FD8CA6523 ON image (relay_point_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F77D93E2D');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FD8CA6523');
        $this->addSql('DROP INDEX IDX_C53D045F77D93E2D ON image');
        $this->addSql('DROP INDEX IDX_C53D045FD8CA6523 ON image');
        $this->addSql('ALTER TABLE image DROP relay_point_id, DROP relay_point_type_id');
    }
}
