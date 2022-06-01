<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210624153231 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F1381F816');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FF7A2C2FC');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FFF745EC3');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F1381F816 FOREIGN KEY (badge_image_light_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FF7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FFF745EC3 FOREIGN KEY (badge_image_id) REFERENCES badge (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FF7A2C2FC');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FFF745EC3');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F1381F816');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FF7A2C2FC FOREIGN KEY (badge_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FFF745EC3 FOREIGN KEY (badge_image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F1381F816 FOREIGN KEY (badge_image_light_id) REFERENCES image (id)');
    }
}
