import LockIcon from '@material-ui/icons/Lock';

import { RightList } from './RightList';
import { RightShow } from './RightShow';
import { RightCreate } from './RightCreate';
import { RightEdit } from './RightEdit';

export default {
    options: {
        label: 'Droits'
    },
    list: RightList,
    show: RightShow,
    create: RightCreate,
    edit: RightEdit, 
    icon: LockIcon
};