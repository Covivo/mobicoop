<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200319134004 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('INSERT INTO `action` (`id`, `name`, `position`) VALUES (33, \'carpool_proposal_minor_updated\', 0);');
        $this->addSql('INSERT INTO `action` (`id`, `name`, `position`) VALUES (34, \'carpool_proposal_major_updated\', 0);');

        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `position`) VALUES (84, 33, 2, null, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `position`) VALUES (85, 33, 3, null, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `position`) VALUES (86, 34, 2, null, 1, 0);');
        $this->addSql('INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_body`, `active`, `position`) VALUES (87, 34, 3, null, 1, 0);');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
