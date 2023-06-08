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
        $this->addSql('INSERT INTO `notification` (id, action_id, medium_id, active, created_date, user_active_default, user_editable, `position`) VALUES (162, 132, 2, 1, "2023-06-07 14:03:52", 1, 0, 0), (163, 132, 3, 1, "2023-06-07 14:03:52", 1, 0, 0), (164, 132, 4, 1, "2023-06-07 14:03:52", 1, 0, 0), (165, 133, 2, 1, "2023-06-07 14:03:52", 1, 0, 0), (166, 133, 3, 1, "2023-06-07 14:03:52", 1, 0, 0), (167, 133, 4, 1, "2023-06-07 14:03:52", 1, 0, 0)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
