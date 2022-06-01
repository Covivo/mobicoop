<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200715121528 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE carpool_payment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, amount NUMERIC(6, 2) NOT NULL, status SMALLINT NOT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_4E75FFB3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carpool_payment_carpool_item (carpool_payment_id INT NOT NULL, carpool_item_id INT NOT NULL, INDEX IDX_41DE3AA51212FDF6 (carpool_payment_id), INDEX IDX_41DE3AA5313229E0 (carpool_item_id), PRIMARY KEY(carpool_payment_id, carpool_item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carpool_item (id INT AUTO_INCREMENT NOT NULL, ask_id INT DEFAULT NULL, debtor_user_id INT DEFAULT NULL, creditor_user_id INT DEFAULT NULL, type SMALLINT NOT NULL, item_status SMALLINT NOT NULL, item_date DATE NOT NULL, amount NUMERIC(6, 2) NOT NULL, debtor_status SMALLINT NOT NULL, creditor_status SMALLINT NOT NULL, created_date DATETIME NOT NULL, updated_date DATETIME DEFAULT NULL, unpaid_date DATETIME DEFAULT NULL, INDEX IDX_3843443DB93F8B63 (ask_id), INDEX IDX_3843443D95399A71 (debtor_user_id), INDEX IDX_3843443D27616656 (creditor_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carpool_payment ADD CONSTRAINT FK_4E75FFB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_payment_carpool_item ADD CONSTRAINT FK_41DE3AA51212FDF6 FOREIGN KEY (carpool_payment_id) REFERENCES carpool_payment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_payment_carpool_item ADD CONSTRAINT FK_41DE3AA5313229E0 FOREIGN KEY (carpool_item_id) REFERENCES carpool_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443DB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443D95399A71 FOREIGN KEY (debtor_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443D27616656 FOREIGN KEY (creditor_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_payment_carpool_item DROP FOREIGN KEY FK_41DE3AA51212FDF6');
        $this->addSql('ALTER TABLE carpool_payment_carpool_item DROP FOREIGN KEY FK_41DE3AA5313229E0');
        $this->addSql('DROP TABLE carpool_payment');
        $this->addSql('DROP TABLE carpool_payment_carpool_item');
        $this->addSql('DROP TABLE carpool_item');
    }
}
