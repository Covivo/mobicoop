import { ShowGuesser, CreateGuesser } from '@api-platform/admin/lib';
import MapIcon from '@material-ui/icons/Map';
import AdressList from './AdressList';
import AddressEdit from './AddressEdit';

export default {
  options: {
    label: 'Adresse',
  },
  list: AdressList,
  show: ShowGuesser,
  create: CreateGuesser,
  edit: AddressEdit,
  icon: MapIcon,
};
