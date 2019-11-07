import PeopleIcon from '@material-ui/icons/People';

import { CommunityList } from './CommunityList';
import { CommunityShow } from './CommunityShow';
import { CommunityCreate } from './CommunityCreate';
import { CommunityEdit } from './CommunityEdit';

export default {
    options: {
        label: 'Communautés'
    },
    list: CommunityList,
    show: CommunityShow,
    create: CommunityCreate,
    edit: CommunityEdit, 
    icon: PeopleIcon
};