import SupervisorAccountIcon from '@material-ui/icons/SupervisorAccount';

import { RoleList } from './RoleList';
import { RoleShow } from './RoleShow';
import { RoleCreate } from './RoleCreate';
import { RoleEdit } from './RoleEdit';

export default {
    options: {
        label: 'Rôles'
    },
    list: RoleList,
    show: RoleShow,
    create: RoleCreate,
    edit: RoleEdit, 
    icon: SupervisorAccountIcon
};