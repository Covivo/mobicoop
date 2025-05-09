<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211012131648 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // fix corrupted database
        $this->addSql('
        delete from log where
            (user_id is not null and user_id not in (select id from user)) or
            (action_id is not null and action_id not in (select id from action)) or
            (user_delegate_id is not null and user_delegate_id not in (select id from user)) or
            (proposal_id is not null and proposal_id not in (select id from proposal)) or
            (matching_id is not null and matching_id not in (select id from matching)) or
            (ask_id is not null and ask_id not in (select id from ask)) or
            (article_id is not null and article_id not in (select id from article)) or
            (event_id is not null and event_id not in (select id from event)) or
            (community_id is not null and community_id not in (select id from community)) or
            (solidary_id is not null and solidary_id not in (select id from solidary)) or
            (territory_id is not null and territory_id not in (select id from territory)) or
            (car_id is not null and car_id not in (select id from car)) or
            (user_related_id is not null and user_related_id not in (select id from user)) or
            (message_id is not null and message_id not in (select id from message)) or
            (campaign_id is not null and campaign_id not in (select id from campaign)) or
            (carpool_payment_id is not null and carpool_payment_id not in (select id from carpool_payment)) or
            (carpool_item_id is not null and carpool_item_id not in (select id from carpool_item));
        ');
        $this->addSql('delete from diary where
            (action_id is not null and action_id not in (select id from action)) or
            (user_id is not null and user_id not in (select id from user)) or
            (author_id is not null and author_id not in (select id from user)) or
            (solidary_id is not null and solidary_id not in (select id from solidary)) or
            (solidary_solution_id is not null and solidary_solution_id not in (select id from solidary_solution));
        ');
        $this->addSql('delete from user_auth_assignment where
            (user_id not in (select id from user)) or
            (auth_item_id not in (select id from auth_item)) or
            (territory_id is not null and territory_id not in (select id from territory));
        ');
        $this->addSql('update 
            proposal p 
            left join proposal pl ON pl.id = p.proposal_linked_id
            set p.criteria_id = null, p.proposal_linked_id = null, p.type = 1, pl.proposal_linked_id = null, pl.type = 1
            where p.criteria_id not in (select id from criteria);
        ');
        $this->addSql('update proposal set event_id = null where event_id is not null and event_id not in (select id from event);');
        $this->addSql('update proposal set subject_id = null where subject_id is not null and subject_id not in (select id from subject);');
        $this->addSql('update proposal set user_delegate_id = null where user_delegate_id is not null and user_delegate_id not in (select id from user);');
        $this->addSql('update proposal set app_delegate_id = null where app_delegate_id is not null and app_delegate_id not in (select id from app);');
        $this->addSql('delete from ask where 
            (criteria_id not in (select id from criteria)) or
            (user_id not in (select id from user)) or
            (user_related_id not in (select id from user));
        ');
        $this->addSql('update ask set user_delegate_id = null where user_delegate_id is not null and user_delegate_id not in (select id from user);');
        $this->addSql('update ask set ask_id = null where ask_id is not null and ask_id not in (select id from ask);');
        $this->addSql('update carpool_proof set driver_id = null where driver_id is not null and driver_id not in (select id from user);');
        $this->addSql('update carpool_proof set passenger_id = null where passenger_id is not null and passenger_id not in (select id from user);');
        $this->addSql('delete from ask_history where ask_id not in (select id from ask);');
        $this->addSql('delete from waypoint where 
            (proposal_id is not null and proposal_id not in (select id from proposal)) or 
            (matching_id is not null and matching_id not in (select id from matching)) or 
            (ask_id is not null and ask_id not in (select id from ask));
        ');
        $this->addSql('update matching set matching_linked_id = null where matching_linked_id is not null and matching_linked_id not in (select id from matching);');
        $this->addSql('update matching set matching_opposite_id = null where matching_opposite_id is not null and matching_opposite_id not in (select id from matching);');
        $this->addSql('delete from criteria where 
            id not in (select criteria_id from ask) and 
            id not in (select criteria_id from matching) and 
            id not in (select criteria_id from proposal) and 
            id not in (select criteria_id from solidary_ask) and 
            id not in (select criteria_id from solidary_matching);
        ');
        $this->addSql('delete from position where 
            (proposal_id not in (select id from proposal)) or 
            (waypoint_id not in (select id from waypoint)) or 
            (direction_id not in (select id from direction));
        ');
        $this->addSql('update message set user_delegate_id = null where user_delegate_id is not null and user_delegate_id not in (select id from user);');
        $this->addSql('delete from message where message_id is not null and message_id not in (select id from message);');
        $this->addSql('delete from notified where
            (notification_id not in (select id from notification)) or 
            (user_id not in (select id from user)) or 
            (ask_history_id is not null and ask_history_id not in (select id from ask_history)) or 
            (matching_id is not null and matching_id not in (select id from matching)) or 
            (recipient_id is not null and recipient_id not in (select id from recipient)) or 
            (community_id is not null and community_id not in (select id from community));
        ');
        $this->addSql('delete from recipient where 
            (user_id not in (select id from user)) or
            (message_id not in (select id from message));
        ');
        $this->addSql('delete from community_user where 
            (community_id not in (select id from community)) or 
            (user_id not in (select id from user));
        ');
        $this->addSql('delete from address where (user_id is not null and user_id not in (select id from user));');
        $this->addSql('delete from address where
            user_id is null and 
            id not in (select address_id from waypoint) and
            id not in (select address_id from community) and 
            id not in (select address_id from event) and 
            id not in (select address_id from relay_point) and 
            id not in (select address_id from solidary_user) and 
            id not in (select personal_address_id from mass_person where personal_address_id is not null) and
            id not in (select work_address_id from mass_person where work_address_id is not null) and 
            id not in (select pick_up_passenger_address_id from carpool_proof where pick_up_passenger_address_id is not null) and 
            id not in (select pick_up_driver_address_id from carpool_proof where pick_up_driver_address_id is not null) and
            id not in (select drop_off_passenger_address_id from carpool_proof where drop_off_passenger_address_id is not null) and
            id not in (select drop_off_driver_address_id from carpool_proof where drop_off_driver_address_id is not null) and 
            id not in (select origin_driver_address_id from carpool_proof where origin_driver_address_id is not null) and 
            id not in (select destination_driver_address_id from carpool_proof where destination_driver_address_id is not null);
        ');
        $this->addSql('delete from image where 
            (community_id is not null and community_id not in (select id from community)) or
            (event_id is not null and event_id not in (select id from event)) or
            (relay_point_id is not null and relay_point_id not in (select id from relay_point)) or
            (relay_point_type_id is not null and relay_point_type_id not in (select id from relay_point_type)) or
            (user_id is not null and user_id not in (select id from user)) or
            (campaign_id is not null and campaign_id not in (select id from campaign)) or
            (badge_icon_id is not null and badge_icon_id not in (select id from badge)) or
            (badge_image_id is not null and badge_image_id not in (select id from badge)) or
            (badge_image_light_id is not null and badge_image_light_id not in (select id from badge)) or
            (editorial_id is not null and editorial_id not in (select id from editorial)) or
            (badge_decorated_icon_id is not null and badge_decorated_icon_id not in (select id from badge));
        ');
        $this->addSql('update mass set community_id = null where community_id is not null and community_id not in (select id from community);');
        $this->addSql('update mass_person set user_id = null where user_id is not null and user_id not in (select id from user);');
        $this->addSql('update mass_person set proposal_id = null where proposal_id is not null and proposal_id not in (select id from proposal);');
        $this->addSql('delete from reward where user_id is not null and user_id not in (select id from user);');
        $this->addSql('delete from reward_step where user_id is not null and user_id not in (select id from user);');
        $this->addSql('delete from proof where solidary_user_structure_id is not null and solidary_user_structure_id not in (select id from solidary_user_structure);');

        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE29D32F035');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2A76ED395');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2B77A2899');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2E92CE751');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2F675F31B');
        $this->addSql('ALTER TABLE diary CHANGE author_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE29D32F035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2B77A2899 FOREIGN KEY (solidary_solution_id) REFERENCES solidary_solution (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C51212FDF6');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C523107D10');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5313229E0');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5537A1329');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C571F7E88B');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C57294869C');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C573F74AD4');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C59D32F035');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5B39876B8');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5B93F8B63');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5C3C6F69F');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5E60506ED');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5E92CE751');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5F4792058');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5F639F774');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5FDA7B0BF');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C51212FDF6 FOREIGN KEY (carpool_payment_id) REFERENCES carpool_payment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C523107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5313229E0 FOREIGN KEY (carpool_item_id) REFERENCES carpool_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C57294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C573F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C59D32F035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5E60506ED FOREIGN KEY (user_related_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_auth_assignment DROP FOREIGN KEY FK_3C1C25815C4B72AD');
        $this->addSql('ALTER TABLE user_auth_assignment DROP FOREIGN KEY FK_3C1C258173F74AD4');
        $this->addSql('ALTER TABLE user_auth_assignment DROP FOREIGN KEY FK_3C1C2581A76ED395');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C25815C4B72AD FOREIGN KEY (auth_item_id) REFERENCES auth_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C258173F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C2581A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947223107D10');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947223EDC87');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947271F7E88B');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472990BEA15');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472A76ED395');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472D3EE9239');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947223107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947223EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472D3EE9239 FOREIGN KEY (app_delegate_id) REFERENCES app (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE023107D10');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0990BEA15');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0A76ED395');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0B93F8B63');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0E60506ED');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE023107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0E60506ED FOREIGN KEY (user_related_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ask_history DROP FOREIGN KEY FK_F4597A9537A1329');
        $this->addSql('ALTER TABLE ask_history DROP FOREIGN KEY FK_F4597A9B93F8B63');
        $this->addSql('ALTER TABLE ask_history ADD CONSTRAINT FK_F4597A9537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ask_history ADD CONSTRAINT FK_F4597A9B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE4502E565');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEC3423909');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE4502E565 FOREIGN KEY (passenger_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEC3423909 FOREIGN KEY (driver_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81272787F3');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81C3C6F69F');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81272787F3 FOREIGN KEY (ptjourney_id) REFERENCES ptjourney (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE individual_stop DROP FOREIGN KEY FK_71948C05F4792058');
        $this->addSql('ALTER TABLE individual_stop ADD CONSTRAINT FK_71948C05F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289304C8BD3');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289990BEA15');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289B29D48C6');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289304C8BD3 FOREIGN KEY (proposal_request_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289B29D48C6 FOREIGN KEY (proposal_offer_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE position DROP INDEX UNIQ_462CE4F5F4792058, ADD INDEX IDX_462CE4F5F4792058 (proposal_id)');
        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F57BB1FD97');
        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F5F4792058');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F57BB1FD97 FOREIGN KEY (waypoint_id) REFERENCES waypoint (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881B39876B8');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881B93F8B63');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881F4792058');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F23107D10');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F537A1329');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA76ED395');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F23107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9D32F035');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAE252B6A5');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9D32F035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4885E0A12');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4A76ED395');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4B39876B8');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4E92F8F78');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4EF1A9D84');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4F4792058');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4FDA7B0BF');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4885E0A12 FOREIGN KEY (ask_history_id) REFERENCES ask_history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49537A1329');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49A76ED395');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE community DROP FOREIGN KEY FK_1B604033A76ED395');
        $this->addSql('ALTER TABLE community CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE community ADD CONSTRAINT FK_1B604033A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE community_user DROP FOREIGN KEY FK_4CC23C83A76ED395');
        $this->addSql('ALTER TABLE community_user DROP FOREIGN KEY FK_4CC23C83FDA7B0BF');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA77987212D');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA77987212D FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE gamification_action DROP FOREIGN KEY FK_32E2E03B5B6C5A7F');
        $this->addSql('ALTER TABLE gamification_action DROP FOREIGN KEY FK_32E2E03B9D32F035');
        $this->addSql('ALTER TABLE gamification_action ADD CONSTRAINT FK_32E2E03B5B6C5A7F FOREIGN KEY (gamification_action_rule_id) REFERENCES gamification_action_rule (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE gamification_action ADD CONSTRAINT FK_32E2E03B9D32F035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253A76ED395');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253F7A2C2FC');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward_step DROP FOREIGN KEY FK_42A86EA726659B86');
        $this->addSql('ALTER TABLE reward_step DROP FOREIGN KEY FK_42A86EA7A76ED395');
        $this->addSql('ALTER TABLE reward_step ADD CONSTRAINT FK_42A86EA726659B86 FOREIGN KEY (sequence_item_id) REFERENCES sequence_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward_step ADD CONSTRAINT FK_42A86EA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sequence_item DROP FOREIGN KEY FK_229992D45E05BFCB');
        $this->addSql('ALTER TABLE sequence_item DROP FOREIGN KEY FK_229992D4F7A2C2FC');
        $this->addSql('ALTER TABLE sequence_item ADD CONSTRAINT FK_229992D45E05BFCB FOREIGN KEY (gamification_action_id) REFERENCES gamification_action (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sequence_item ADD CONSTRAINT FK_229992D4F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81A76ED395');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE translate DROP FOREIGN KEY FK_4A10637782F1BAF4');
        $this->addSql('ALTER TABLE translate ADD CONSTRAINT FK_4A10637782F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F1381F816');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F523DDE8E');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F71F7E88B');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F77D93E2D');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FA76ED395');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FBAF1A24D');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FD8CA6523');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FDBEE983D');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FF639F774');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FFDA7B0BF');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FFF745EC3');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F1381F816 FOREIGN KEY (badge_image_light_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F523DDE8E FOREIGN KEY (badge_icon_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F77D93E2D FOREIGN KEY (relay_point_id) REFERENCES relay_point (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FBAF1A24D FOREIGN KEY (editorial_id) REFERENCES editorial (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FD8CA6523 FOREIGN KEY (relay_point_type_id) REFERENCES relay_point_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FDBEE983D FOREIGN KEY (badge_decorated_icon_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FF639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FFF745EC3 FOREIGN KEY (badge_image_id) REFERENCES badge (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDA76ED395');
        $this->addSql('ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDDD103342');
        $this->addSql('ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDE252B6A5');
        $this->addSql('ALTER TABLE campaign CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDDD103342 FOREIGN KEY (campaign_template_id) REFERENCES campaign_template (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDE252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE campaign_template DROP FOREIGN KEY FK_A510C9FCE252B6A5');
        $this->addSql('ALTER TABLE campaign_template ADD CONSTRAINT FK_A510C9FCE252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10A76ED395');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10F639F774');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass DROP FOREIGN KEY FK_6C035B66A76ED395');
        $this->addSql('ALTER TABLE mass DROP FOREIGN KEY FK_6C035B66FDA7B0BF');
        $this->addSql('ALTER TABLE mass CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mass ADD CONSTRAINT FK_6C035B66A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE mass ADD CONSTRAINT FK_6C035B66FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE mass_person DROP FOREIGN KEY FK_75908575A76ED395');
        $this->addSql('ALTER TABLE mass_person DROP FOREIGN KEY FK_75908575F4792058');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpool_item DROP FOREIGN KEY FK_3843443D27616656');
        $this->addSql('ALTER TABLE carpool_item DROP FOREIGN KEY FK_3843443D95399A71');
        $this->addSql('ALTER TABLE carpool_item DROP FOREIGN KEY FK_3843443DB93F8B63');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443D27616656 FOREIGN KEY (creditor_user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443D95399A71 FOREIGN KEY (debtor_user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443DB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE carpool_payment DROP FOREIGN KEY FK_4E75FFB3A76ED395');
        $this->addSql('ALTER TABLE carpool_payment ADD CONSTRAINT FK_4E75FFB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment_profile DROP FOREIGN KEY FK_981EA4E5A76ED395');
        $this->addSql('ALTER TABLE payment_profile ADD CONSTRAINT FK_981EA4E5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptarrival DROP FOREIGN KEY FK_F839CBDBF5B7AF75');
        $this->addSql('ALTER TABLE ptarrival ADD CONSTRAINT FK_F839CBDBF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptdeparture DROP FOREIGN KEY FK_C9E71422F5B7AF75');
        $this->addSql('ALTER TABLE ptdeparture ADD CONSTRAINT FK_C9E71422F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptjourney DROP FOREIGN KEY FK_6BCA51CD4D48F7F9');
        $this->addSql('ALTER TABLE ptjourney DROP FOREIGN KEY FK_6BCA51CD90969994');
        $this->addSql('ALTER TABLE ptjourney ADD CONSTRAINT FK_6BCA51CD4D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptjourney ADD CONSTRAINT FK_6BCA51CD90969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C7944272787F3');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C79444D48F7F9');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C794490969994');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C7944B6A6325B');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C7944EEA7E22D');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944272787F3 FOREIGN KEY (ptjourney_id) REFERENCES ptjourney (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C79444D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C794490969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944EEA7E22D FOREIGN KEY (ptline_id) REFERENCES ptline (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptline DROP FOREIGN KEY FK_46E281876575144A');
        $this->addSql('ALTER TABLE ptline DROP FOREIGN KEY FK_46E28187B6A6325B');
        $this->addSql('ALTER TABLE ptline ADD CONSTRAINT FK_46E281876575144A FOREIGN KEY (ptcompany_id) REFERENCES ptcompany (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptline ADD CONSTRAINT FK_46E28187B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4D4D48F7F9');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4D90969994');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4DC0405E45');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4D4D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4D90969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4DC0405E45 FOREIGN KEY (ptleg_id) REFERENCES ptleg (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9C2534008B');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9CA76ED395');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9CD8CA6523');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9CFDA7B0BF');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9C2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CD8CA6523 FOREIGN KEY (relay_point_type_id) REFERENCES relay_point_type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE relay_point_type DROP FOREIGN KEY FK_4F8712254B9D732');
        $this->addSql('ALTER TABLE relay_point_type ADD CONSTRAINT FK_4F8712254B9D732 FOREIGN KEY (icon_id) REFERENCES icon (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE need DROP FOREIGN KEY FK_E6F46C44E92CE751');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C44E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operate DROP FOREIGN KEY FK_44CAF6AB2534008B');
        $this->addSql('ALTER TABLE operate DROP FOREIGN KEY FK_44CAF6ABA76ED395');
        $this->addSql('ALTER TABLE operate ADD CONSTRAINT FK_44CAF6AB2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operate ADD CONSTRAINT FK_44CAF6ABA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD860DC45F');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DDE51EA2A6');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD860DC45F FOREIGN KEY (structure_proof_id) REFERENCES structure_proof (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DDE51EA2A6 FOREIGN KEY (solidary_user_structure_id) REFERENCES solidary_user_structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA423EDC87');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4E51EA2A6');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4F4792058');
        $this->addSql('ALTER TABLE solidary CHANGE subject_id subject_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA423EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4E51EA2A6 FOREIGN KEY (solidary_user_structure_id) REFERENCES solidary_user_structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6B77A2899');
        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6B93F8B63');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6B77A2899 FOREIGN KEY (solidary_solution_id) REFERENCES solidary_solution (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_ask_history DROP FOREIGN KEY FK_A4345EB3537A1329');
        $this->addSql('ALTER TABLE solidary_ask_history DROP FOREIGN KEY FK_A4345EB3550BE553');
        $this->addSql('ALTER TABLE solidary_ask_history ADD CONSTRAINT FK_A4345EB3537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE solidary_ask_history ADD CONSTRAINT FK_A4345EB3550BE553 FOREIGN KEY (solidary_ask_id) REFERENCES solidary_ask (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411815BD757');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411B39876B8');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411E92CE751');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF4318E9BFA2');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF43E92CE751');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF4318E9BFA2 FOREIGN KEY (solidary_matching_id) REFERENCES solidary_matching (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF43E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_user_structure DROP FOREIGN KEY FK_436AB1AE2534008B');
        $this->addSql('ALTER TABLE solidary_user_structure DROP FOREIGN KEY FK_436AB1AE815BD757');
        $this->addSql('ALTER TABLE solidary_user_structure ADD CONSTRAINT FK_436AB1AE2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solidary_user_structure ADD CONSTRAINT FK_436AB1AE815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EA2534008B');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE structure_proof DROP FOREIGN KEY FK_2E281B4B2534008B');
        $this->addSql('ALTER TABLE structure_proof ADD CONSTRAINT FK_2E281B4B2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7A2534008B');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A2534008B FOREIGN KEY (structure_id) REFERENCES structure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B97221EBCBB63');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722A76ED395');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B97221EBCBB63 FOREIGN KEY (blocked_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DA76ED395');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE push_token DROP FOREIGN KEY FK_51BC1381A76ED395');
        $this->addSql('ALTER TABLE push_token ADD CONSTRAINT FK_51BC1381A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C65254E55');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C670574616');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C65254E55 FOREIGN KEY (reviewed_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C670574616 FOREIGN KEY (reviewer_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64923107D10');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649815BD757');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64982F1BAF4');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D3EE9239');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64923107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D3EE9239 FOREIGN KEY (app_delegate_id) REFERENCES app (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_notification DROP FOREIGN KEY FK_3F980AC8A76ED395');
        $this->addSql('ALTER TABLE user_notification DROP FOREIGN KEY FK_3F980AC8EF1A9D84');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81A76ED395');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0A76ED395');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0E60506ED');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE023107D10');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0B93F8B63');
        $this->addSql('ALTER TABLE ask DROP FOREIGN KEY FK_6826EAE0990BEA15');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0E60506ED FOREIGN KEY (user_related_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE023107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE ask ADD CONSTRAINT FK_6826EAE0990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ask_history DROP FOREIGN KEY FK_F4597A9B93F8B63');
        $this->addSql('ALTER TABLE ask_history DROP FOREIGN KEY FK_F4597A9537A1329');
        $this->addSql('ALTER TABLE ask_history ADD CONSTRAINT FK_F4597A9B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE ask_history ADD CONSTRAINT FK_F4597A9537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722A76ED395');
        $this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B97221EBCBB63');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B97221EBCBB63 FOREIGN KEY (blocked_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDE252B6A5');
        $this->addSql('ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDDD103342');
        $this->addSql('ALTER TABLE campaign DROP FOREIGN KEY FK_1F1512DDA76ED395');
        $this->addSql('ALTER TABLE campaign CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDE252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDDD103342 FOREIGN KEY (campaign_template_id) REFERENCES campaign_template (id)');
        $this->addSql('ALTER TABLE campaign ADD CONSTRAINT FK_1F1512DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE campaign_template DROP FOREIGN KEY FK_A510C9FCE252B6A5');
        $this->addSql('ALTER TABLE campaign_template ADD CONSTRAINT FK_A510C9FCE252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69DA76ED395');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_item DROP FOREIGN KEY FK_3843443DB93F8B63');
        $this->addSql('ALTER TABLE carpool_item DROP FOREIGN KEY FK_3843443D95399A71');
        $this->addSql('ALTER TABLE carpool_item DROP FOREIGN KEY FK_3843443D27616656');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443DB93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443D95399A71 FOREIGN KEY (debtor_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_item ADD CONSTRAINT FK_3843443D27616656 FOREIGN KEY (creditor_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_payment DROP FOREIGN KEY FK_4E75FFB3A76ED395');
        $this->addSql('ALTER TABLE carpool_payment ADD CONSTRAINT FK_4E75FFB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CEC3423909');
        $this->addSql('ALTER TABLE carpool_proof DROP FOREIGN KEY FK_59B969CE4502E565');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CEC3423909 FOREIGN KEY (driver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE carpool_proof ADD CONSTRAINT FK_59B969CE4502E565 FOREIGN KEY (passenger_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE community DROP FOREIGN KEY FK_1B604033A76ED395');
        $this->addSql('ALTER TABLE community CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE community ADD CONSTRAINT FK_1B604033A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE community_user DROP FOREIGN KEY FK_4CC23C83FDA7B0BF');
        $this->addSql('ALTER TABLE community_user DROP FOREIGN KEY FK_4CC23C83A76ED395');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE community_user ADD CONSTRAINT FK_4CC23C83A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81C3C6F69F');
        $this->addSql('ALTER TABLE criteria DROP FOREIGN KEY FK_B61F9B81272787F3');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('ALTER TABLE criteria ADD CONSTRAINT FK_B61F9B81272787F3 FOREIGN KEY (ptjourney_id) REFERENCES ptjourney (id)');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10F639F774');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10A76ED395');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE29D32F035');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2A76ED395');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2F675F31B');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2E92CE751');
        $this->addSql('ALTER TABLE diary DROP FOREIGN KEY FK_917BEDE2B77A2899');
        $this->addSql('ALTER TABLE diary CHANGE author_id author_id INT NOT NULL');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE29D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
        $this->addSql('ALTER TABLE diary ADD CONSTRAINT FK_917BEDE2B77A2899 FOREIGN KEY (solidary_solution_id) REFERENCES solidary_solution (id)');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA77987212D');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA77987212D FOREIGN KEY (app_id) REFERENCES app (id)');
        $this->addSql('ALTER TABLE gamification_action DROP FOREIGN KEY FK_32E2E03B9D32F035');
        $this->addSql('ALTER TABLE gamification_action DROP FOREIGN KEY FK_32E2E03B5B6C5A7F');
        $this->addSql('ALTER TABLE gamification_action ADD CONSTRAINT FK_32E2E03B9D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
        $this->addSql('ALTER TABLE gamification_action ADD CONSTRAINT FK_32E2E03B5B6C5A7F FOREIGN KEY (gamification_action_rule_id) REFERENCES gamification_action_rule (id)');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F71F7E88B');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FFDA7B0BF');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FA76ED395');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F77D93E2D');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FD8CA6523');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F523DDE8E');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FDBEE983D');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FFF745EC3');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F1381F816');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FF639F774');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FBAF1A24D');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F77D93E2D FOREIGN KEY (relay_point_id) REFERENCES relay_point (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FD8CA6523 FOREIGN KEY (relay_point_type_id) REFERENCES relay_point_type (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F523DDE8E FOREIGN KEY (badge_icon_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FDBEE983D FOREIGN KEY (badge_decorated_icon_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FFF745EC3 FOREIGN KEY (badge_image_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F1381F816 FOREIGN KEY (badge_image_light_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FF639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FBAF1A24D FOREIGN KEY (editorial_id) REFERENCES editorial (id)');
        $this->addSql('ALTER TABLE individual_stop DROP FOREIGN KEY FK_71948C05F4792058');
        $this->addSql('ALTER TABLE individual_stop ADD CONSTRAINT FK_71948C05F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C59D32F035');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C523107D10');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5F4792058');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5B39876B8');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5B93F8B63');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C57294869C');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C571F7E88B');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5FDA7B0BF');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5E92CE751');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C573F74AD4');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5C3C6F69F');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5E60506ED');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5537A1329');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5F639F774');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C51212FDF6');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5313229E0');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C59D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C523107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C57294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C571F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C573F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5E60506ED FOREIGN KEY (user_related_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C51212FDF6 FOREIGN KEY (carpool_payment_id) REFERENCES carpool_payment (id)');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5313229E0 FOREIGN KEY (carpool_item_id) REFERENCES carpool_item (id)');
        $this->addSql('ALTER TABLE mass DROP FOREIGN KEY FK_6C035B66A76ED395');
        $this->addSql('ALTER TABLE mass DROP FOREIGN KEY FK_6C035B66FDA7B0BF');
        $this->addSql('ALTER TABLE mass CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE mass ADD CONSTRAINT FK_6C035B66A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mass ADD CONSTRAINT FK_6C035B66FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE mass_person DROP FOREIGN KEY FK_75908575A76ED395');
        $this->addSql('ALTER TABLE mass_person DROP FOREIGN KEY FK_75908575F4792058');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mass_person ADD CONSTRAINT FK_75908575F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289B29D48C6');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289304C8BD3');
        $this->addSql('ALTER TABLE matching DROP FOREIGN KEY FK_DC10F289990BEA15');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289B29D48C6 FOREIGN KEY (proposal_offer_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289304C8BD3 FOREIGN KEY (proposal_request_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE matching ADD CONSTRAINT FK_DC10F289990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA76ED395');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F23107D10');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F537A1329');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F23107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE need DROP FOREIGN KEY FK_E6F46C44E92CE751');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C44E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9D32F035');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAE252B6A5');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE252B6A5 FOREIGN KEY (medium_id) REFERENCES medium (id)');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4EF1A9D84');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4A76ED395');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4F4792058');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4FDA7B0BF');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4B39876B8');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4885E0A12');
        $this->addSql('ALTER TABLE notified DROP FOREIGN KEY FK_D23269D4E92F8F78');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4FDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4885E0A12 FOREIGN KEY (ask_history_id) REFERENCES ask_history (id)');
        $this->addSql('ALTER TABLE notified ADD CONSTRAINT FK_D23269D4E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id)');
        $this->addSql('ALTER TABLE operate DROP FOREIGN KEY FK_44CAF6AB2534008B');
        $this->addSql('ALTER TABLE operate DROP FOREIGN KEY FK_44CAF6ABA76ED395');
        $this->addSql('ALTER TABLE operate ADD CONSTRAINT FK_44CAF6AB2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE operate ADD CONSTRAINT FK_44CAF6ABA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment_profile DROP FOREIGN KEY FK_981EA4E5A76ED395');
        $this->addSql('ALTER TABLE payment_profile ADD CONSTRAINT FK_981EA4E5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE position DROP INDEX IDX_462CE4F5F4792058, ADD UNIQUE INDEX UNIQ_462CE4F5F4792058 (proposal_id)');
        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F5F4792058');
        $this->addSql('ALTER TABLE position DROP FOREIGN KEY FK_462CE4F57BB1FD97');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F5F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE position ADD CONSTRAINT FK_462CE4F57BB1FD97 FOREIGN KEY (waypoint_id) REFERENCES waypoint (id)');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DD860DC45F');
        $this->addSql('ALTER TABLE proof DROP FOREIGN KEY FK_FBF940DDE51EA2A6');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DD860DC45F FOREIGN KEY (structure_proof_id) REFERENCES structure_proof (id)');
        $this->addSql('ALTER TABLE proof ADD CONSTRAINT FK_FBF940DDE51EA2A6 FOREIGN KEY (solidary_user_structure_id) REFERENCES solidary_user_structure (id)');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472A76ED395');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947223107D10');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472D3EE9239');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472990BEA15');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947271F7E88B');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE5947223EDC87');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947223107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472D3EE9239 FOREIGN KEY (app_delegate_id) REFERENCES app (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472990BEA15 FOREIGN KEY (criteria_id) REFERENCES criteria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947271F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE5947223EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id)');
        $this->addSql('ALTER TABLE ptarrival DROP FOREIGN KEY FK_F839CBDBF5B7AF75');
        $this->addSql('ALTER TABLE ptarrival ADD CONSTRAINT FK_F839CBDBF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE ptdeparture DROP FOREIGN KEY FK_C9E71422F5B7AF75');
        $this->addSql('ALTER TABLE ptdeparture ADD CONSTRAINT FK_C9E71422F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE ptjourney DROP FOREIGN KEY FK_6BCA51CD4D48F7F9');
        $this->addSql('ALTER TABLE ptjourney DROP FOREIGN KEY FK_6BCA51CD90969994');
        $this->addSql('ALTER TABLE ptjourney ADD CONSTRAINT FK_6BCA51CD4D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id)');
        $this->addSql('ALTER TABLE ptjourney ADD CONSTRAINT FK_6BCA51CD90969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id)');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C7944272787F3');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C79444D48F7F9');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C794490969994');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C7944B6A6325B');
        $this->addSql('ALTER TABLE ptleg DROP FOREIGN KEY FK_513C7944EEA7E22D');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944272787F3 FOREIGN KEY (ptjourney_id) REFERENCES ptjourney (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C79444D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C794490969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE ptleg ADD CONSTRAINT FK_513C7944EEA7E22D FOREIGN KEY (ptline_id) REFERENCES ptline (id)');
        $this->addSql('ALTER TABLE ptline DROP FOREIGN KEY FK_46E281876575144A');
        $this->addSql('ALTER TABLE ptline DROP FOREIGN KEY FK_46E28187B6A6325B');
        $this->addSql('ALTER TABLE ptline ADD CONSTRAINT FK_46E281876575144A FOREIGN KEY (ptcompany_id) REFERENCES ptcompany (id)');
        $this->addSql('ALTER TABLE ptline ADD CONSTRAINT FK_46E28187B6A6325B FOREIGN KEY (travel_mode_id) REFERENCES travel_mode (id)');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4DC0405E45');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4D4D48F7F9');
        $this->addSql('ALTER TABLE ptstep DROP FOREIGN KEY FK_D44FCB4D90969994');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4DC0405E45 FOREIGN KEY (ptleg_id) REFERENCES ptleg (id)');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4D4D48F7F9 FOREIGN KEY (ptdeparture_id) REFERENCES ptdeparture (id)');
        $this->addSql('ALTER TABLE ptstep ADD CONSTRAINT FK_D44FCB4D90969994 FOREIGN KEY (ptarrival_id) REFERENCES ptarrival (id)');
        $this->addSql('ALTER TABLE push_token DROP FOREIGN KEY FK_51BC1381A76ED395');
        $this->addSql('ALTER TABLE push_token ADD CONSTRAINT FK_51BC1381A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49A76ED395');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49537A1329');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9CA76ED395');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9CFDA7B0BF');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9C2534008B');
        $this->addSql('ALTER TABLE relay_point DROP FOREIGN KEY FK_A9BE6C9CD8CA6523');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9C2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE relay_point ADD CONSTRAINT FK_A9BE6C9CD8CA6523 FOREIGN KEY (relay_point_type_id) REFERENCES relay_point_type (id)');
        $this->addSql('ALTER TABLE relay_point_type DROP FOREIGN KEY FK_4F8712254B9D732');
        $this->addSql('ALTER TABLE relay_point_type ADD CONSTRAINT FK_4F8712254B9D732 FOREIGN KEY (icon_id) REFERENCES icon (id)');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C670574616');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C65254E55');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C670574616 FOREIGN KEY (reviewer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C65254E55 FOREIGN KEY (reviewed_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253F7A2C2FC');
        $this->addSql('ALTER TABLE reward DROP FOREIGN KEY FK_4ED17253A76ED395');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE reward ADD CONSTRAINT FK_4ED17253A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reward_step DROP FOREIGN KEY FK_42A86EA726659B86');
        $this->addSql('ALTER TABLE reward_step DROP FOREIGN KEY FK_42A86EA7A76ED395');
        $this->addSql('ALTER TABLE reward_step ADD CONSTRAINT FK_42A86EA726659B86 FOREIGN KEY (sequence_item_id) REFERENCES sequence_item (id)');
        $this->addSql('ALTER TABLE reward_step ADD CONSTRAINT FK_42A86EA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sequence_item DROP FOREIGN KEY FK_229992D4F7A2C2FC');
        $this->addSql('ALTER TABLE sequence_item DROP FOREIGN KEY FK_229992D45E05BFCB');
        $this->addSql('ALTER TABLE sequence_item ADD CONSTRAINT FK_229992D4F7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
        $this->addSql('ALTER TABLE sequence_item ADD CONSTRAINT FK_229992D45E05BFCB FOREIGN KEY (gamification_action_id) REFERENCES gamification_action (id)');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4E51EA2A6');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA4F4792058');
        $this->addSql('ALTER TABLE solidary DROP FOREIGN KEY FK_1896BCA423EDC87');
        $this->addSql('ALTER TABLE solidary CHANGE subject_id subject_id INT NOT NULL');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4E51EA2A6 FOREIGN KEY (solidary_user_structure_id) REFERENCES solidary_user_structure (id)');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA4F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE solidary ADD CONSTRAINT FK_1896BCA423EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id)');
        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6B77A2899');
        $this->addSql('ALTER TABLE solidary_ask DROP FOREIGN KEY FK_1F77CFF6B93F8B63');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6B77A2899 FOREIGN KEY (solidary_solution_id) REFERENCES solidary_solution (id)');
        $this->addSql('ALTER TABLE solidary_ask ADD CONSTRAINT FK_1F77CFF6B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
        $this->addSql('ALTER TABLE solidary_ask_history DROP FOREIGN KEY FK_A4345EB3550BE553');
        $this->addSql('ALTER TABLE solidary_ask_history DROP FOREIGN KEY FK_A4345EB3537A1329');
        $this->addSql('ALTER TABLE solidary_ask_history ADD CONSTRAINT FK_A4345EB3550BE553 FOREIGN KEY (solidary_ask_id) REFERENCES solidary_ask (id)');
        $this->addSql('ALTER TABLE solidary_ask_history ADD CONSTRAINT FK_A4345EB3537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411B39876B8');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411815BD757');
        $this->addSql('ALTER TABLE solidary_matching DROP FOREIGN KEY FK_A95C6411E92CE751');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('ALTER TABLE solidary_matching ADD CONSTRAINT FK_A95C6411E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF43E92CE751');
        $this->addSql('ALTER TABLE solidary_solution DROP FOREIGN KEY FK_EA7FBF4318E9BFA2');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF43E92CE751 FOREIGN KEY (solidary_id) REFERENCES solidary (id)');
        $this->addSql('ALTER TABLE solidary_solution ADD CONSTRAINT FK_EA7FBF4318E9BFA2 FOREIGN KEY (solidary_matching_id) REFERENCES solidary_matching (id)');
        $this->addSql('ALTER TABLE solidary_user_structure DROP FOREIGN KEY FK_436AB1AE815BD757');
        $this->addSql('ALTER TABLE solidary_user_structure DROP FOREIGN KEY FK_436AB1AE2534008B');
        $this->addSql('ALTER TABLE solidary_user_structure ADD CONSTRAINT FK_436AB1AE815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('ALTER TABLE solidary_user_structure ADD CONSTRAINT FK_436AB1AE2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE structure DROP FOREIGN KEY FK_6F0137EA2534008B');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE structure_proof DROP FOREIGN KEY FK_2E281B4B2534008B');
        $this->addSql('ALTER TABLE structure_proof ADD CONSTRAINT FK_2E281B4B2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7A2534008B');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A2534008B FOREIGN KEY (structure_id) REFERENCES structure (id)');
        $this->addSql('ALTER TABLE translate DROP FOREIGN KEY FK_4A10637782F1BAF4');
        $this->addSql('ALTER TABLE translate ADD CONSTRAINT FK_4A10637782F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64982F1BAF4');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64923107D10');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D3EE9239');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649815BD757');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64923107D10 FOREIGN KEY (user_delegate_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D3EE9239 FOREIGN KEY (app_delegate_id) REFERENCES app (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649815BD757 FOREIGN KEY (solidary_user_id) REFERENCES solidary_user (id)');
        $this->addSql('ALTER TABLE user_auth_assignment DROP FOREIGN KEY FK_3C1C2581A76ED395');
        $this->addSql('ALTER TABLE user_auth_assignment DROP FOREIGN KEY FK_3C1C25815C4B72AD');
        $this->addSql('ALTER TABLE user_auth_assignment DROP FOREIGN KEY FK_3C1C258173F74AD4');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C2581A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C25815C4B72AD FOREIGN KEY (auth_item_id) REFERENCES auth_item (id)');
        $this->addSql('ALTER TABLE user_auth_assignment ADD CONSTRAINT FK_3C1C258173F74AD4 FOREIGN KEY (territory_id) REFERENCES territory (id)');
        $this->addSql('ALTER TABLE user_notification DROP FOREIGN KEY FK_3F980AC8EF1A9D84');
        $this->addSql('ALTER TABLE user_notification DROP FOREIGN KEY FK_3F980AC8A76ED395');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8EF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881F4792058');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881B39876B8');
        $this->addSql('ALTER TABLE waypoint DROP FOREIGN KEY FK_B3DC5881B93F8B63');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881F4792058 FOREIGN KEY (proposal_id) REFERENCES proposal (id)');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881B39876B8 FOREIGN KEY (matching_id) REFERENCES matching (id)');
        $this->addSql('ALTER TABLE waypoint ADD CONSTRAINT FK_B3DC5881B93F8B63 FOREIGN KEY (ask_id) REFERENCES ask (id)');
    }
}
