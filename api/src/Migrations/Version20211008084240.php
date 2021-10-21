<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211008084240 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_item ADD debtor_consumption_feedback_return_code INT DEFAULT NULL, ADD debtor_consumption_feedback_external_id VARCHAR(255) DEFAULT NULL, ADD debtor_consumption_feedback_date DATETIME DEFAULT NULL, ADD creditor_consumption_feedback_return_code INT DEFAULT NULL, ADD creditor_consumption_feedback_external_id VARCHAR(255) DEFAULT NULL, ADD creditor_consumption_feedback_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE carpool_item DROP debtor_consumption_feedback_return_code, DROP debtor_consumption_feedback_external_id, DROP debtor_consumption_feedback_date, DROP creditor_consumption_feedback_return_code, DROP creditor_consumption_feedback_external_id, DROP creditor_consumption_feedback_date');
    }
}
