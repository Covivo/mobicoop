<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210713131913 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253A76ED395');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253F7A2C2FC');
        $this->addSql('ALTER TABLE reward ADD id INT AUTO_INCREMENT NOT NULL, ADD created_date DATETIME DEFAULT NULL, ADD updated_date DATETIME DEFAULT NULL, ADD notified_date DATETIME DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE `reward` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT FIRST');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reward MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253F7A2C2FC');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253A76ED395');
        $this->addSql('ALTER TABLE reward DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE reward DROP id, DROP created_date, DROP updated_date, DROP notified_date');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward ADD PRIMARY KEY (badge_id, user_id)');
    }
}
