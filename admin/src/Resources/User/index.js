import UserIcon from '@material-ui/icons/Person';
import UserList from './UserList';
import UserCreate from './UserCreate';
import UserEdit from './UserEdit';

export default {
  options: {
    label: 'Utilisateurs',
  },
  list: UserList,
  create: UserCreate,
  edit: UserEdit,
  icon: UserIcon,
};
