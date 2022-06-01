<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210804100333 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FF7A2C2FC');
        $this->addSql('DROP INDEX UNIQ_C53D045FF7A2C2FC ON image');
        $this->addSql('ALTER TABLE image ADD badge_decorated_icon_id INT DEFAULT NULL, CHANGE badge_id badge_icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F523DDE8E FOREIGN KEY (badge_icon_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FDBEE983D FOREIGN KEY (badge_decorated_icon_id) REFERENCES badge (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045F523DDE8E ON image (badge_icon_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045FDBEE983D ON image (badge_decorated_icon_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F523DDE8E');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FDBEE983D');
        $this->addSql('DROP INDEX UNIQ_C53D045F523DDE8E ON image');
        $this->addSql('DROP INDEX UNIQ_C53D045FDBEE983D ON image');
        $this->addSql('ALTER TABLE image ADD badge_id INT DEFAULT NULL, DROP badge_icon_id, DROP badge_decorated_icon_id');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FF7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045FF7A2C2FC ON image (badge_id)');
    }
}
