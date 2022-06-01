<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190521083701 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_matching DROP INDEX UNIQ_9B3B75EB8577578, ADD INDEX IDX_9B3B75EB8577578 (mass_person2_id)');
        $this->addSql('ALTER TABLE mass_matching DROP INDEX UNIQ_9B3B75EB1AE2DA96, ADD INDEX IDX_9B3B75EB1AE2DA96 (mass_person1_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_matching DROP INDEX IDX_9B3B75EB1AE2DA96, ADD UNIQUE INDEX UNIQ_9B3B75EB1AE2DA96 (mass_person1_id)');
        $this->addSql('ALTER TABLE mass_matching DROP INDEX IDX_9B3B75EB8577578, ADD UNIQUE INDEX UNIQ_9B3B75EB8577578 (mass_person2_id)');
    }
}
