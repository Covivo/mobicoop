<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Import item migration.
 */
final class Version20220314152000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `badge` SET `text` = 'Publier une annonce est l\\'acte fondateur du covoiturage, félicitations !\r\nSi vous avez publié une annonce Conducteur, avez-vous envisagé à la passer en \"Peu importe\" afin de pouvoir le cas échéant être passager et augmenter vos chances de trouver un covoitureur ?' WHERE `badge`.`id` = 2");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
