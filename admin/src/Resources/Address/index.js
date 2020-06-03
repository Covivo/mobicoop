import MapIcon from '@material-ui/icons/Map';

import AdressList from './AdressList';
import AddressEdit from './AddressEdit';

export default {
  options: {
    label: 'Adresse',
  },
  list: AdressList,
  edit: AddressEdit,
  icon: MapIcon,
};
