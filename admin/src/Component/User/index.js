import UserIcon from '@material-ui/icons/Person';

import { UserList } from './UserList';
import { UserShow } from './UserShow';
import { UserCreate } from './UserCreate';
import { UserEdit } from './UserEdit';

import isAuthorized from '../Utilities/authorization';

let components = {
    list: isAuthorized("user_manage") ? UserList : undefined,
    show: isAuthorized("user_manage") ? UserShow : undefined,
    create: isAuthorized("user_create") ? UserCreate : undefined,
    edit: isAuthorized("user_update") ? UserEdit : undefined,
    icon: UserIcon
};

export default {
    options: {
        label: 'Utilisateurs'
    },
    ...components
};