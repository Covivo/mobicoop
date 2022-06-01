<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220415115837 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE identity_proof DROP FOREIGN KEY FK_11A20A2D642B8210');
        $this->addSql('ALTER TABLE identity_proof DROP FOREIGN KEY FK_11A20A2DA76ED395');
        $this->addSql('ALTER TABLE identity_proof ADD CONSTRAINT FK_11A20A2D642B8210 FOREIGN KEY (admin_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE identity_proof ADD CONSTRAINT FK_11A20A2DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE identity_proof DROP FOREIGN KEY FK_11A20A2DA76ED395');
        $this->addSql('ALTER TABLE identity_proof DROP FOREIGN KEY FK_11A20A2D642B8210');
        $this->addSql('ALTER TABLE identity_proof ADD CONSTRAINT FK_11A20A2DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE identity_proof ADD CONSTRAINT FK_11A20A2D642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
    }
}
