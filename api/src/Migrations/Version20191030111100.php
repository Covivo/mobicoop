<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Manual migration for renaming carpool_proposal_canceled.
 */
final class Version20191030111100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `action` SET name=\'carpool_proposal_canceled\' WHERE ID=12;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
