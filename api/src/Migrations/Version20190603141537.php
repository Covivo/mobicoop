<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190603141537 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE uright DROP INDEX UNIQ_98C5BFD8727ACA70, ADD INDEX IDX_98C5BFD8727ACA70 (parent_id)');
        $this->addSql('ALTER TABLE role DROP INDEX UNIQ_57698A6A727ACA70, ADD INDEX IDX_57698A6A727ACA70 (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE role DROP INDEX IDX_57698A6A727ACA70, ADD UNIQUE INDEX UNIQ_57698A6A727ACA70 (parent_id)');
        $this->addSql('ALTER TABLE uright DROP INDEX IDX_98C5BFD8727ACA70, ADD UNIQUE INDEX UNIQ_98C5BFD8727ACA70 (parent_id)');
    }
}
