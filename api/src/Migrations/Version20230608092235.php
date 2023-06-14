<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230608092235 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Remove the solidary rights from the admin role
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=2 AND child_id=133');
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=2 AND child_id=195');
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=2 AND child_id=237');
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=2 AND child_id=274');

        // Add the right to modify its own structure to the solidary operator
        $this->addSql('INSERT INTO auth_item_child (parent_id, child_id) VALUES (10, 192)');

        // Add the right to edit structures to the solidary admin
        $this->addSql('INSERT INTO auth_item_child (parent_id, child_id) VALUES (274, 195)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('INSERT INTO auth_item_child (parent_id, child_id) VALUES (2, 133), (2, 195), (2, 237), (2, 274)');

        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=10 AND child_id=192');
        $this->addSql('DELETE FROM auth_item_child WHERE parent_id=274 AND child_id=195');
    }
}
