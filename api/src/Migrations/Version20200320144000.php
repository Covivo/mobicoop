<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200320144000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
        INSERT INTO `auth_rule` (`id`, `name`) VALUES
        (21, \'ProofOwner\');
        ');

        $this->addSql("
        INSERT INTO `auth_item` (`id`, `auth_rule_id`, `type`, `name`, `description`) VALUES
        (173, NULL, 1, 'solidary_user_structure_create', 'Create a SolidaryUserStructure'),
        (174, NULL, 1, 'solidary_user_structure_read', 'Read a SolidaryUserStructure'),
        (175, NULL, 1, 'solidary_user_structure_update', 'Update a SolidaryUserStructure'),
        (176, NULL, 1, 'solidary_user_structure_delete', 'Delete a SolidaryUserStructure'),
        (177, NULL, 1, 'solidary_user_structure_list', 'List the SolidaryUserStructure'),
        (178, NULL, 1, 'solidary_user_structure_manage', 'Manage the SolidaryUserStructures'),
        (179, NULL, 1, 'proof_create', 'Create a proof'),
        (180, 21, 1, 'proof_create_self', 'Create its own proof'),
        (181, NULL, 1, 'proof_read', 'Read a proof'),
        (182, 21, 1, 'proof_read_self', 'Read its own proof'),
        (183, NULL, 1, 'proof_update', 'Update a proof'),
        (184, 21, 1, 'proof_update_self', 'Update its own proof'),
        (185, NULL, 1, 'proof_delete', 'Delete a proof'),
        (186, 21, 1, 'proof_delete_self', 'Delete its own proof'),
        (187, NULL, 1, 'proof_list', 'List the proofs'),
        (188, NULL, 1, 'proof_list_self', 'List its owns Proofs'),
        (189, NULL, 1, 'proof_manage', 'Manage the prooves'),
        (190, NULL, 1, 'structure_create', 'Create a structure'),
        (191, NULL, 1, 'structure_read', 'Read a structure'),
        (192, NULL, 1, 'structure_update', 'Update a structure'),
        (193, NULL, 1, 'structure_delete', 'Delete a structure'),
        (194, NULL, 1, 'structure_list', 'List the structures'),
        (195, NULL, 1, 'structure_manage', 'Manage the structures'),
        (196, NULL, 1, 'subject_create', 'Create a subject'),
        (197, NULL, 1, 'subject_read', 'Read a subject'),
        (198, NULL, 1, 'subject_update', 'Update a subject'),
        (199, NULL, 1, 'subject_delete', 'Delete a subject'),
        (200, NULL, 1, 'subject_list', 'List the subjects'),
        (201, NULL, 1, 'subject_manage', 'Manage the subjects'),
        (202, 4, 1, 'ad_read_self', 'View its own Ad');
        ");

        $this->addSql('
        INSERT INTO `auth_item_child` (`parent_id`, `child_id`) VALUES
        (10, 22),
        (178,173),
        (178,174),
        (178,175),
        (178,176),
        (178,177),
        (10,178),
        (171,180),
        (171,182),
        (171,184),
        (171,186),
        (171,188),
        (172,180),
        (172,182),
        (172,184),
        (172,186),
        (172,188),
        (180,179),
        (182,181),
        (184,183),
        (186,185),
        (188,187),
        (189,179),
        (189,181),
        (189,183),
        (189,185),
        (189,187),
        (10,189),
        (195,190),
        (195,191),
        (195,192),
        (195,193),
        (195,194),
        (10,195),
        (201,196),
        (201,197),
        (201,198),
        (201,199),
        (201,200),
        (10,201),
        (4,202),
        (202,34);
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
