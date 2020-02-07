<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200207163126 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('UPDATE `uright` SET parent_id = 1 WHERE id = 28;');
        $this->addSql('UPDATE `uright` SET parent_id = 1 WHERE id = 29;');
        $this->addSql('UPDATE `uright` SET parent_id = 19 WHERE id = 30;');
        $this->addSql('UPDATE `uright` SET parent_id = 20, check_ownership = 1 WHERE id = 31;');
        $this->addSql('UPDATE `uright` SET parent_id = 21, check_ownership = 1 WHERE id = 32;');
        $this->addSql('UPDATE `uright` SET parent_id = 22, check_ownership = 1 WHERE id = 33;');
        $this->addSql('UPDATE `uright` SET parent_id = 23, check_ownership = 1 WHERE id = 34;');
        $this->addSql('UPDATE `uright` SET parent_id = 24, check_ownership = 1 WHERE id = 35;');
        $this->addSql('UPDATE `uright` SET parent_id = 25, check_ownership = 1 WHERE id = 36;');
        $this->addSql('UPDATE `uright` SET parent_id = 26, check_ownership = 1 WHERE id = 37;');
        $this->addSql('UPDATE `uright` SET parent_id = 27, check_ownership = 1 WHERE id = 38;');
        $this->addSql('UPDATE `uright` SET parent_id = 7 WHERE id = 42;');
        $this->addSql('UPDATE `uright` SET parent_id = 39 WHERE id = 43;');
        $this->addSql('UPDATE `uright` SET parent_id = 40, check_ownership = 1 WHERE id = 45;');
        $this->addSql('UPDATE `uright` SET parent_id = 41, check_ownership = 1 WHERE id = 46;');
        $this->addSql('UPDATE `uright` SET parent_id = 9 WHERE id = 50;');
        $this->addSql('UPDATE `uright` SET parent_id = 10 WHERE id = 51;');
        $this->addSql('UPDATE `uright` SET parent_id = 53, check_ownership = 1 WHERE id = 57;');
        $this->addSql('UPDATE `uright` SET parent_id = 54, check_ownership = 1 WHERE id = 58;');
        $this->addSql('UPDATE `uright` SET parent_id = 10 WHERE id = 59;');
        $this->addSql('UPDATE `uright` SET parent_id = 10 WHERE id = 60;');
        $this->addSql('UPDATE `uright` SET parent_id = 10 WHERE id = 61;');
        $this->addSql('UPDATE `uright` SET parent_id = 10 WHERE id = 62;');
        $this->addSql('UPDATE `uright` SET parent_id = 12 WHERE id = 63;');
        $this->addSql('UPDATE `uright` SET parent_id = 64, check_ownership = 1 WHERE id = 66;');
        $this->addSql('UPDATE `uright` SET parent_id = 65, check_ownership = 1 WHERE id = 67;');
        $this->addSql('UPDATE `uright` SET parent_id = 14 WHERE id = 74;');
        $this->addSql('UPDATE `uright` SET parent_id = 17 WHERE id = 83;');
        $this->addSql('UPDATE `uright` SET parent_id = 10 WHERE id = 85;');
        $this->addSql('UPDATE `uright` SET parent_id = 86, check_ownership = 1 WHERE id = 87;');
        $this->addSql('UPDATE `uright` SET parent_id = 88, check_ownership = 1 WHERE id = 89;');
        $this->addSql('UPDATE `uright` SET parent_id = 90, check_ownership = 1 WHERE id = 91;');
        $this->addSql('UPDATE `uright` SET parent_id = 12 WHERE id = 92;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        
    }
}
