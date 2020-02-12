import {ShowGuesser, CreateGuesser} from '@api-platform/admin/lib';
import AdressList from './AdressList';
import AddressEdit from './AddressEdit';
import MapIcon from '@material-ui/icons/Map';

export default {
    options: {
        label: 'Adresse'
    },
    list: AdressList,
    show: ShowGuesser,
    create: CreateGuesser,
    edit: AddressEdit, 
    icon: MapIcon
};
