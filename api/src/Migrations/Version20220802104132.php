<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220802104132 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE `notification` SET active=0 WHERE ID=139;');
        $this->addSql('UPDATE `notification` SET active=0 WHERE ID=140;');
        $this->addSql('UPDATE `notification` SET active=0 WHERE ID=141;');
        $this->addSql('UPDATE `notification` SET active=0 WHERE ID=142;');
        $this->addSql('UPDATE `notification` SET active=0 WHERE ID=143;');
        $this->addSql('UPDATE `notification` SET active=0 WHERE ID=144;');
        $this->addSql('UPDATE `notification` SET active=0 WHERE ID=145;');
        $this->addSql('UPDATE `notification` SET active=0 WHERE ID=146;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
