import EventIcon from '@material-ui/icons/Event';

import { EventList } from './EventList';
// import { TerritoryShow } from './TerritoryShow';
// import { TerritoryCreate } from './TerritoryCreate';
// import { TerritoryEdit } from './TerritoryEdit';

export default {
    options: {
        label: 'Evénements'
    },
    list: EventList,
    // show: TerritoryShow,
    // create: TerritoryCreate,
    // edit: TerritoryEdit, 
    icon: EventIcon
};