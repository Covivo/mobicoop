import Solidary from '@material-ui/icons/LocalTaxi';

import SolidaryView from './SolidaryView';
import SolidaryCreate from './SolidaryCreate';
import { SolidaryList } from './SolidaryList';

export default {
  options: {
    label: 'Demandes solidaires',
  },
  list: SolidaryList,
  show: SolidaryView,
  create: SolidaryCreate,
  icon: Solidary,
};
