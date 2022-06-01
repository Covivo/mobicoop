<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * List of relay points
 */
final class Version20200529110000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // get the list of relay points is accessible for all
        $this->addSql('UPDATE auth_item_child set parent_id=\'5\', child_id=\'89\' WHERE parent_id=\'90\' AND child_id=\'89\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
