import {useState } from 'react';
import { useDataProvider } from 'react-admin';

// function to search for a given permission
// todo : refactor with authProvider function
function isAuthorized(action) {
    if (localStorage.getItem('permissions')) {
        let permissions = JSON.parse(localStorage.getItem('permissions'));
        return permissions.hasOwnProperty(action);
    }
    return false;
}

const useCurrentUserId = () => {
    const dataProvider      = useDataProvider();
    const [user, setUser]   = useState(null);

    if (localStorage.getItem('id')) {
        // That should be made on the back-end side for security reason
        dataProvider.getOne('users',{id:localStorage.getItem('id')} )
            .then( ({ data }) => setUser(data) )
            .catch( error => {
                setUser(null)
                console.log("Erreur lors de la recherche de l'utilisateur courant :", error)
            })
    }

    return user;
}

export { isAuthorized, useCurrentUserId}

