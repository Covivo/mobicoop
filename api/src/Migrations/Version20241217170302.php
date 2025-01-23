<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241217170302 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        // désactiver par defaut toutes les notifications SMS
        $this->addSql('update
                            notification
                        set
                            user_active_default = 0
                        where
                            medium_id = 3');
    }

    public function down(Schema $schema): void
    {
    }
}
