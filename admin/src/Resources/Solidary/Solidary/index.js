import Solidary from '@material-ui/icons/LocalTaxi';

import ListGuesser from '@api-platform/admin/lib/ListGuesser';

import SolidaryView from './SolidaryView';
import SolidaryCreate from './SolidaryCreate';

export default {
  options: {
    label: 'Demandes solidaires',
  },
  list: ListGuesser,
  show: SolidaryView,
  create: SolidaryCreate,
  icon: Solidary,
};
