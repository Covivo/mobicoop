<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial Gamification Model Migration.
 */
final class Version20210622084802 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, status INT NOT NULL, public TINYINT(1) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE badge_territory (badge_id INT NOT NULL, territory_id INT NOT NULL, INDEX IDX_D8D866F2F7A2C2FC (badge_id), INDEX IDX_D8D866F273F74AD4 (territory_id), PRIMARY KEY(badge_id, territory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reward (badge_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4ED17253F7A2C2FC (badge_id), INDEX IDX_4ED17253A76ED395 (user_id), PRIMARY KEY(badge_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gamification_action (id INT AUTO_INCREMENT NOT NULL, action_id INT NOT NULL, gamification_action_rule_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_32E2E03B9D32F035 (action_id), INDEX IDX_32E2E03B5B6C5A7F (gamification_action_rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gamification_action_rule (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reward_step (id INT AUTO_INCREMENT NOT NULL, sequence_item SMALLINT NOT NULL, user INT DEFAULT NULL, created_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sequence_item (id INT AUTO_INCREMENT NOT NULL, badge_id INT NOT NULL, gamification_action_id INT NOT NULL, position SMALLINT NOT NULL, min_count INT DEFAULT NULL, min_unique_count INT DEFAULT NULL, in_date_range TINYINT(1) DEFAULT NULL, INDEX IDX_229992D4F7A2C2FC (badge_id), INDEX IDX_229992D45E05BFCB (gamification_action_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE badge_territory ADD CONSTRAINT FK_D8D866F2F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE badge_territory ADD CONSTRAINT FK_D8D866F273F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gamification_action ADD CONSTRAINT FK_32E2E03B9D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
        $this->addSql('ALTER TABLE gamification_action ADD CONSTRAINT FK_32E2E03B5B6C5A7F FOREIGN KEY (gamification_action_rule_id) REFERENCES gamification_action_rule (id)');
        $this->addSql('ALTER TABLE sequence_item ADD CONSTRAINT FK_229992D4F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE sequence_item ADD CONSTRAINT FK_229992D45E05BFCB FOREIGN KEY (gamification_action_id) REFERENCES gamification_action (id)');
        $this->addSql('ALTER TABLE image ADD badge_id INT DEFAULT NULL, ADD badge_image_id INT DEFAULT NULL, ADD badge_image_light_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FF7A2C2FC FOREIGN KEY (badge_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FFF745EC3 FOREIGN KEY (badge_image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F1381F816 FOREIGN KEY (badge_image_light_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045FF7A2C2FC ON image (badge_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045FFF745EC3 ON image (badge_image_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045F1381F816 ON image (badge_image_light_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE badge_territory DROP FOREIGN KEY FK_D8D866F2F7A2C2FC');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253F7A2C2FC');
        $this->addSql('ALTER TABLE sequence_item DROP FOREIGN KEY FK_229992D4F7A2C2FC');
        $this->addSql('ALTER TABLE sequence_item DROP FOREIGN KEY FK_229992D45E05BFCB');
        $this->addSql('ALTER TABLE gamification_action DROP FOREIGN KEY FK_32E2E03B5B6C5A7F');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE badge_territory');
        $this->addSql('DROP TABLE reward');
        $this->addSql('DROP TABLE gamification_action');
        $this->addSql('DROP TABLE gamification_action_rule');
        $this->addSql('DROP TABLE reward_step');
        $this->addSql('DROP TABLE sequence_item');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FF7A2C2FC');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FFF745EC3');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F1381F816');
        $this->addSql('DROP INDEX UNIQ_C53D045FF7A2C2FC ON image');
        $this->addSql('DROP INDEX UNIQ_C53D045FFF745EC3 ON image');
        $this->addSql('DROP INDEX UNIQ_C53D045F1381F816 ON image');
        $this->addSql('ALTER TABLE image DROP badge_id, DROP badge_image_id, DROP badge_image_light_id');
    }
}
