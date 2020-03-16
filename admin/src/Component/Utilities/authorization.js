import {useState } from 'react';
import { useDataProvider } from 'react-admin';

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

export { useCurrentUserId}

