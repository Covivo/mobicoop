<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220301111200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("INSERT INTO `article` (`id`, `title`, `status`, `created_date`, `updated_date`, `i_frame`) VALUES (37, 'GOOD_PRACTICES_ALT', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `article` (`id`, `title`, `status`, `created_date`, `updated_date`, `i_frame`) VALUES (38, 'FAQ_ALT', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `article` (`id`, `title`, `status`, `created_date`, `updated_date`, `i_frame`) VALUES (39, 'CGU_ALT', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `article` (`id`, `title`, `status`, `created_date`, `updated_date`, `i_frame`) VALUES (40, 'DATA_POLICY_ALT', '1', NULL, NULL, NULL);");
        $this->addSql("INSERT INTO `article` (`id`, `title`, `status`, `created_date`, `updated_date`, `i_frame`) VALUES (41, 'DATA_PROTECTION_ALT', '1', NULL, NULL, NULL);");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_payment CHANGE transaction_id transaction_id INT DEFAULT NULL');
    }
}
