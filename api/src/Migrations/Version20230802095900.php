<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230802095900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        // Add ROLE_SOLIDARY_BENEFICIARY to all user with ROLE_SOLIDARY_BENEFICIARY_CANDIDATE and no ROLE_SOLIDARY_BENEFICIARY
        $this->addSql('INSERT INTO user_auth_assignment (user_id, auth_item_id)
            select
                user.id,
                12
            from
                user
                inner join user_auth_assignment on user_auth_assignment.user_id = user.id
            where
                (
                    user_auth_assignment.auth_item_id = 12
                    or user_auth_assignment.auth_item_id = 172
                )
                and user.id in (
                    select
                        user.id
                    from
                        user
                        inner join user_auth_assignment on user_auth_assignment.user_id = user.id
                    where
                        user_auth_assignment.auth_item_id = 172
                )
                and user.id not in (
                    select
                        user.id
                    from
                        user
                        inner join user_auth_assignment on user_auth_assignment.user_id = user.id
                    where
                        user_auth_assignment.auth_item_id = 12
                )
            order by
                user.id asc');

        // REMOVE all ROLE_SOLIDARY_BENEFICIARY_CANDIDATE
        $this->addSql('DELETE FROM
                            user_auth_assignment
                        WHERE
                            auth_item_id = 172');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
