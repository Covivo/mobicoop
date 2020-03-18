import { fetchUtils } from 'react-admin';

/* List of permissions
"ad_search": [],
    "article_manage": [],
    "article_read": [],
    "carpool_manage": [],
    "carpool_manage_self": [],
    "check_permission": [],
    "check_permission_self": [],
    "community_create": [],
    "community_join": [],
    "community_join_private": [],
    "community_leave": [],
    "community_list": [],
    "community_manage": [],
    "community_manage_self": [],
    "community_read": [],
    "event_create": [],
    "event_list": [],
    "event_manage": [],
    "event_manage_self": [],
    "event_read": [],
    "mass_communication_manage": [],
    "mass_manage": [],
    "relay_point_create": [],
    "relay_point_manage": [],
    "relay_point_manage_self": [],
    "relay_point_read": [],
    "relay_point_type_create": [],
    "solidary_manage": [],
    "territory_manage": [],
    "user_address_manage": [],
    "user_address_manage_self": [],
    "user_car_manage": [],
    "user_car_manage_self": [],
    "user_manage": [],
    "user_manage_self": [],
    "user_message_manage": [],
    "user_message_manage_self": [],
    "user_register": [],
    "user_register_full": []
*/

// function to search for a given permission
export default (action) => {

    let permissions = JSON.parse(localStorage.getItem('permissions'));
    return permissions ? (Object.values(permissions).indexOf(action) > -1) : false;

}
