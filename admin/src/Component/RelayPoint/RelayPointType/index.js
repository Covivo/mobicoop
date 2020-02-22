import LocalParkingIcon from '@material-ui/icons/LocalParking';

import { RelayPointTypeList } from './RelayPointTypeList';
import { RelayPointTypeShow } from './RelayPointTypeShow';
import { RelayPointTypeCreate } from './RelayPointTypeCreate';
import { RelayPointTypeEdit } from './RelayPointTypeEdit';

export default {
    options: {
        label: 'Types de points relais'
    },
    list: RelayPointTypeList,
    show: RelayPointTypeShow,
    create: RelayPointTypeCreate,
    edit: RelayPointTypeEdit, 
    icon: LocalParkingIcon
};