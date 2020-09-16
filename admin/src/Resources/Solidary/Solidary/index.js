import Solidary from '@material-ui/icons/LocalTaxi';

import SolidaryShow from './view/SolidaryShow';
import SolidaryCreate from './create/SolidaryCreate';
import { SolidaryList } from './list/SolidaryList';
import { SolidaryEdit } from './edit/SolidaryEdit';

export default {
  options: {
    label: 'Demandes solidaires',
  },
  list: SolidaryList,
  show: SolidaryShow,
  create: SolidaryCreate,
  icon: Solidary,
  // @UNCOMMENT (22149)
  // edit: SolidaryEdit,
};
