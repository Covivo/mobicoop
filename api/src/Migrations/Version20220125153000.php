<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220125153000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `action` (`id`, `name`, `position`) VALUES 
            (110, 'identity_proof_reminder', 0);
        ");
        $this->addSql("INSERT INTO `notification` (`id`, `action_id`, `medium_id`, `template_title`, `active`, `template_body`, `created_date`, `updated_date`, `user_active_default`, `user_editable`, `position`) VALUES 
            (136, '110', '2', NULL, '1', NULL, NULL, NULL, '1', '0', '0');
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
