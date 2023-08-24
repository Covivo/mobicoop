<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230710121806 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `action` (`id`, `name`, `position`) VALUE (134, 'carpool_ask_accepted_eec', 0)");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `created_date`, `user_active_default`, `user_editable`, `position`) VALUES (168, 134, 3, 1, '2023-07-10 15:00', 1, 0, 0)");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `active`, `created_date`, `user_active_default`, `user_editable`, `position`) VALUES (169, 134, 4, 1, '2023-07-10 15:00', 1, 0, 0)");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM `notification` WHERE id = 168;");
        $this->addSql("DELETE FROM `notification` WHERE id = 169;");
        $this->addSql("DELETE FROM `action` WHERE id = 134;");
    }
}
