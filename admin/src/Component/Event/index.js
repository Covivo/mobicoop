import EventIcon from '@material-ui/icons/Event';

import { EventList } from './EventList';
import { EventShow } from './EventShow';
// import { TerritoryCreate } from './TerritoryCreate';
import { EventEdit } from './EventEdit';
import { EventCreate } from './EventCreate';

export default {
    options: {
        label: 'Événements'
    },
    list: EventList,
    show: EventShow,
    create: EventCreate,
    edit: EventEdit, 
    icon: EventIcon
};