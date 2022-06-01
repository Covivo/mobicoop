<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200318084200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (167, NULL, 1, 'solidary_volunteer_read', 'Read a solidary volunteer'),
        (168, 11, 1, 'solidary_volunteer_read_self', 'Read its own solidary volunteer profile'),
        (169, NULL, 1, 'solidary_beneficiary_read', 'Read a solidary beneficiary'),
        (170, 12    , 1, 'solidary_beneficiary_read_self', 'Read its own solidary beneficiary profile');
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (117, 167),
        (11, 168),
        (168, 167),
        (125, 169),
        (12, 170),
        (170, 169);
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
