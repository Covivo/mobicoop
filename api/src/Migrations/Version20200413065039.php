<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200413065039 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE address_territory (address_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_7335052EF5B7AF75 (address_id), INDEX IDX_7335052E73F74AD4 (territory_id), PRIMARY KEY(address_id, territory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE direction_territory (direction_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_8254FD11AF73D997 (direction_id), INDEX IDX_8254FD1173F74AD4 (territory_id), PRIMARY KEY(direction_id, territory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE address_territory ADD CONSTRAINT FK_7335052EF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE address_territory ADD CONSTRAINT FK_7335052E73F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE direction_territory ADD CONSTRAINT FK_8254FD11AF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE direction_territory ADD CONSTRAINT FK_8254FD1173F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');

        // add territory authorization for selected admins
        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (7, 103),
        (8, 103),
        (9, 103),
        (10, 103),
        (13, 103)
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE address_territory');
        $this->addSql('DROP TABLE direction_territory');
    }
}
