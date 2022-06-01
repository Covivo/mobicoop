<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190809160100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=2;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=3;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=4;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=5;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=6;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=10;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=11;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=12;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=13;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=14;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=15;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=16;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=17;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=18;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=19;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=20;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=21;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=22;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=23;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=24;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=25;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=26;');
        $this->addSql('UPDATE `action` SET in_log=1 WHERE ID=27;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=2;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=3;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=4;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=5;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=6;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=10;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=11;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=12;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=13;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=14;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=15;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=16;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=17;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=18;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=19;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=20;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=21;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=22;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=23;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=24;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=25;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=26;');
        $this->addSql('UPDATE `action` SET in_log=null WHERE ID=27;');
    }
}
