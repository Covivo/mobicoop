<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200307180232 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_role DROP FOREIGN KEY FK_5247AFCAD60322AC');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6A727ACA70');
        $this->addSql('ALTER TABLE role_right DROP FOREIGN KEY FK_43169D3BD60322AC');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE role_right DROP FOREIGN KEY FK_43169D3B54976835');
        $this->addSql('ALTER TABLE user_right DROP FOREIGN KEY FK_56088E4C54976835');
        $this->addSql('CREATE TABLE app_auth_item (app_id INT NOT NULL, auth_item_id INT NOT NULL, INDEX IDX_99529A9F7987212D (app_id), INDEX IDX_99529A9F5C4B72AD (auth_item_id), PRIMARY KEY(app_id, auth_item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auth_item (id INT AUTO_INCREMENT NOT NULL, auth_rule_id INT DEFAULT NULL, type INT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_313DC5AA3A6A23A2 (auth_rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auth_item_child (parent_id INT NOT NULL, child_id INT NOT NULL, INDEX IDX_1611424D727ACA70 (parent_id), INDEX IDX_1611424DDD62C21B (child_id), PRIMARY KEY(parent_id, child_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auth_rule (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_auth_assignment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, auth_item_id INT NOT NULL, territory_id INT DEFAULT NULL, INDEX IDX_3C1C2581A76ED395 (user_id), INDEX IDX_3C1C25815C4B72AD (auth_item_id), INDEX IDX_3C1C258173F74AD4 (territory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_auth_item ADD CONSTRAINT FK_99529A9F7987212D FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_auth_item ADD CONSTRAINT FK_99529A9F5C4B72AD FOREIGN KEY (auth_item_id) REFERENCES auth_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE auth_item ADD CONSTRAINT FK_313DC5AA3A6A23A2 FOREIGN KEY (auth_rule_id) REFERENCES auth_rule (id)');
        $this->addSql('ALTER TABLE auth_item_child ADD CONSTRAINT FK_1611424D727ACA70 FOREIGN KEY (parent_id) REFERENCES auth_item (id)');
        $this->addSql('ALTER TABLE auth_item_child ADD CONSTRAINT FK_1611424DDD62C21B FOREIGN KEY (child_id) REFERENCES auth_item (id)');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C2581A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C25815C4B72AD FOREIGN KEY (auth_item_id) REFERENCES auth_item (id)');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C258173F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('DROP TABLE app_role');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_right');
        $this->addSql('DROP TABLE uright');
        $this->addSql('DROP TABLE user_right');
        $this->addSql('DROP TABLE user_role');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_auth_item DROP FOREIGN KEY FK_99529A9F5C4B72AD');
        $this->addSql('ALTER TABLE auth_item_child DROP FOREIGN KEY FK_1611424D727ACA70');
        $this->addSql('ALTER TABLE auth_item_child DROP FOREIGN KEY FK_1611424DDD62C21B');
        $this->addSql('ALTER TABLE user_auth_assignment DROP FOREIGN KEY FK_3C1C25815C4B72AD');
        $this->addSql('ALTER TABLE auth_item DROP FOREIGN KEY FK_313DC5AA3A6A23A2');
        $this->addSql('CREATE TABLE app_role (app_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_5247AFCAD60322AC (role_id), INDEX IDX_5247AFCA7987212D (app_id), PRIMARY KEY(app_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci, name VARCHAR(45) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_57698A6A727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role_right (role_id INT NOT NULL, right_id INT NOT NULL, INDEX IDX_43169D3B54976835 (right_id), INDEX IDX_43169D3BD60322AC (role_id), PRIMARY KEY(role_id, right_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE uright (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, description VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, object VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_right (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, right_id INT NOT NULL, territory_id INT DEFAULT NULL, INDEX IDX_56088E4C73F74AD4 (territory_id), INDEX IDX_56088E4C54976835 (right_id), INDEX IDX_56088E4CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_role (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, role_id INT NOT NULL, territory_id INT DEFAULT NULL, INDEX IDX_2DE8C6A373F74AD4 (territory_id), INDEX IDX_2DE8C6A3D60322AC (role_id), INDEX IDX_2DE8C6A3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE app_role ADD CONSTRAINT FK_5247AFCA7987212D FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_role ADD CONSTRAINT FK_5247AFCAD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A727ACA70 FOREIGN KEY (parent_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_right ADD CONSTRAINT FK_43169D3B54976835 FOREIGN KEY (right_id) REFERENCES uright (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_right ADD CONSTRAINT FK_43169D3BD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4C54976835 FOREIGN KEY (right_id) REFERENCES uright (id)');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4C73F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE user_right ADD CONSTRAINT FK_56088E4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A373F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('DROP TABLE app_auth_item');
        $this->addSql('DROP TABLE auth_item');
        $this->addSql('DROP TABLE auth_item_child');
        $this->addSql('DROP TABLE auth_rule');
        $this->addSql('DROP TABLE user_auth_assignment');
    }
}
