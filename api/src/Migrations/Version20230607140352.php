<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230607140352 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO `action` (id, name, `position`) VALUES (132, 'pay_after_carpool_regular_mon', 0), (133, 'pay_after_carpool_regular_wed', 0)");
        $this->addSql('INSERT INTO `notification` (action_id, medium_id, active, `position`) VALUES (132, 2, 1, 0), (132, 3, 1, 0), (132, 4, 1, 0), (133, 2, 1, 0), (133, 3, 1, 0), (133, 4, 1, 0)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
