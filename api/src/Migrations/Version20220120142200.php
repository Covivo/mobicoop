<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220120142200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `action` (`id`, `name`, `position`) VALUES 
            (108, 'identity_proof_moderated_accepted', 0),
            (109, 'identity_proof_moderated_rejected', 0);
        ");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES 
            (134, '108', '2', NULL, '1', NULL, NULL, NULL, '1', '0', '0'),
            (135, '109', '2', NULL, '1', NULL, NULL, NULL, '1', '0', '0');
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
