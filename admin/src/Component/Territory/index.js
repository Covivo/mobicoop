import MapIcon from '@material-ui/icons/Map';

import { TerritoryList } from './TerritoryList';
import { TerritoryShow } from './TerritoryShow';
import { TerritoryCreate } from './TerritoryCreate';
import { TerritoryEdit } from './TerritoryEdit';

export default {
    options: {
        label: 'Territoires'
    },
    list: TerritoryList,
    show: TerritoryShow,
    create: TerritoryCreate,
    edit: TerritoryEdit, 
    icon: MapIcon
};