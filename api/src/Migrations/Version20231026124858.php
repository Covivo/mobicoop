<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231026124858 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_account ADD app_delegate_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sso_account ADD CONSTRAINT FK_FEBF9D40D3EE9239 FOREIGN KEY (app_delegate_id) REFERENCES app (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_FEBF9D40D3EE9239 ON sso_account (app_delegate_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sso_account DROP FOREIGN KEY FK_FEBF9D40D3EE9239');
        $this->addSql('DROP INDEX IDX_FEBF9D40D3EE9239 ON sso_account');
        $this->addSql('ALTER TABLE sso_account DROP app_delegate_id');
    }
}
