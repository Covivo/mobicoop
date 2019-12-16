import EventIcon from '@material-ui/icons/Event';

import { EventList } from './EventList';
import { EventShow } from './EventShow';
// import { TerritoryCreate } from './TerritoryCreate';
// import { TerritoryEdit } from './TerritoryEdit';

export default {
    options: {
        label: 'Evénements'
    },
    list: EventList,
    show: EventShow,
    // create: TerritoryCreate,
    // edit: TerritoryEdit, 
    icon: EventIcon
};