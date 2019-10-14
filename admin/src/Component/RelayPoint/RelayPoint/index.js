import LocalParkingIcon from '@material-ui/icons/LocalParking';

import { RelayPointList } from './RelayPointList';
import { RelayPointShow } from './RelayPointShow';
import { RelayPointCreate } from './RelayPointCreate';
import { RelayPointEdit } from './RelayPointEdit';

export default {
    options: {
        label: 'Points relais'
    },
    list: RelayPointList,
    show: RelayPointShow,
    create: RelayPointCreate,
    edit: RelayPointEdit, 
    icon: LocalParkingIcon
};