<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241217170301 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // désactive les notifications SMS pour les users qui n'ont PAS DE pushs actifs sauf carpool_ask_xxx and new_internal_message
        $this->addSql('update
                            user_notification
                        set
                            user_notification.active = 0
                        where
                            user_notification.user_id not in (
                                SELECT
                                    distinct(user.id)
                                FROM
                                    user
                                    inner join user_notification un on un.user_id = user.id
                                    inner join `notification` on un.notification_id = notification.id
                                where
                                    notification.medium_id = 4
                                    and un.active = 1
                            )
                            and user_notification.notification_id in (
                                select
                                    notification.id
                                from
                                    action
                                    inner join notification on action.id = notification.action_id
                                where
                                    medium_id = 3
                                    and action.id not in (4, 5, 6, 7)
    )');
    }

    public function down(Schema $schema): void
    {
    }
}
