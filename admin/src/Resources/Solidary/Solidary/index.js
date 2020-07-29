import Solidary from '@material-ui/icons/LocalTaxi';

import SolidaryShow from './view/SolidaryShow';
import SolidaryCreate from './create/SolidaryCreate';
import { SolidaryList } from './list/SolidaryList';
// import { SolidaryEdit } from './edit/SolidaryEdit';

export default {
  options: {
    label: 'Demandes solidaires',
  },
  list: SolidaryList,
  show: SolidaryShow,
  create: SolidaryCreate,
  icon: Solidary,
  // The API doesn't handle PUT update for the moment
  // So the EDIT view is disabled for now
  // edit: SolidaryEdit,
};
