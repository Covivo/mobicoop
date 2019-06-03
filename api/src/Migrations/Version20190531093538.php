<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190531093538 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE address_territory');
        $this->addSql('DROP TABLE territory_territory');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE address_territory (address_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_7335052EF5B7AF75 (address_id), INDEX IDX_7335052E73F74AD4 (territory_id), PRIMARY KEY(address_id, territory_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE territory_territory (territory_source INT NOT NULL, territory_target INT NOT NULL, INDEX IDX_44A1E66BCA8FFFF7 (territory_source), INDEX IDX_44A1E66BD36AAF78 (territory_target), PRIMARY KEY(territory_source, territory_target)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE address_territory ADD CONSTRAINT FK_7335052E73F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE address_territory ADD CONSTRAINT FK_7335052EF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territory_territory ADD CONSTRAINT FK_44A1E66BCA8FFFF7 FOREIGN KEY (territory_source) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territory_territory ADD CONSTRAINT FK_44A1E66BD36AAF78 FOREIGN KEY (territory_target) REFERENCES territory (id) ON DELETE CASCADE');
    }
}
