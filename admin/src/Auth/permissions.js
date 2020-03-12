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

    const options = {}
    let val = false;
    const apiPermissions = process.env.REACT_APP_API+'/permissions';
    const httpClient = fetchUtils.fetchJson;
    if (!options.headers) {
        options.headers = new Headers({ Accept: 'application/json' });
    }
    options.headers.set('Authorization', `Bearer ${localStorage.token}`);

    httpClient(apiPermissions, {
        method: 'GET',
        headers : options.headers
    }).then( retour => {
        if (retour.status = '200') {
            // On supprime l'ancienne image ?
            let permissions = retour.json;
            val =  Object.values(permissions).includes(action)
            //console.info(permissions.includes(action))
        }
    });

    return true;


}
