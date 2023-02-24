<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230224134452 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP INDEX IDX_894AD28F1212FDF6, ADD UNIQUE INDEX UNIQ_894AD28F1212FDF6 (carpool_payment_id)');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP FOREIGN KEY FK_894AD28F3827AC9');
        $this->addSql('DROP INDEX IDX_894AD28F3827AC9 ON mobconnect__long_distance_journey');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey CHANGE long_distance_subscription_id subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD CONSTRAINT FK_894AD28F9A1887DC FOREIGN KEY (subscription_id) REFERENCES mobconnect__long_distance_subscription (id)');
        $this->addSql('CREATE INDEX IDX_894AD28F9A1887DC ON mobconnect__long_distance_journey (subscription_id)');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey DROP FOREIGN KEY FK_68CFFDA6375AD4C6');
        $this->addSql('DROP INDEX IDX_68CFFDA6375AD4C6 ON mobconnect__short_distance_journey');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey CHANGE short_distance_subscription_id subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey ADD CONSTRAINT FK_68CFFDA69A1887DC FOREIGN KEY (subscription_id) REFERENCES mobconnect__short_distance_subscription (id)');
        $this->addSql('CREATE INDEX IDX_68CFFDA69A1887DC ON mobconnect__short_distance_journey (subscription_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP INDEX UNIQ_894AD28F1212FDF6, ADD INDEX IDX_894AD28F1212FDF6 (carpool_payment_id)');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey DROP FOREIGN KEY FK_894AD28F9A1887DC');
        $this->addSql('DROP INDEX IDX_894AD28F9A1887DC ON mobconnect__long_distance_journey');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey CHANGE subscription_id long_distance_subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__long_distance_journey ADD CONSTRAINT FK_894AD28F3827AC9 FOREIGN KEY (long_distance_subscription_id) REFERENCES mobconnect__long_distance_subscription (id)');
        $this->addSql('CREATE INDEX IDX_894AD28F3827AC9 ON mobconnect__long_distance_journey (long_distance_subscription_id)');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey DROP FOREIGN KEY FK_68CFFDA69A1887DC');
        $this->addSql('DROP INDEX IDX_68CFFDA69A1887DC ON mobconnect__short_distance_journey');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey CHANGE subscription_id short_distance_subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mobconnect__short_distance_journey ADD CONSTRAINT FK_68CFFDA6375AD4C6 FOREIGN KEY (short_distance_subscription_id) REFERENCES mobconnect__short_distance_subscription (id)');
        $this->addSql('CREATE INDEX IDX_68CFFDA6375AD4C6 ON mobconnect__short_distance_journey (short_distance_subscription_id)');
    }
}
