import UserIcon from '@material-ui/icons/Person';
import { Admin, Resource, EditGuesser } from 'react-admin';
import  UserList  from './UserList';
import { UserShow } from './UserShow';
import  UserCreate  from './UserCreate';
import UserEdit  from './UserEdit';

let components = {
    list: UserList,
    show: UserShow,
    create: UserCreate,
    edit: UserEdit,
    icon: UserIcon
};

export default {
    options: {
        label: 'Utilisateurs'
    },
    ...components
};
