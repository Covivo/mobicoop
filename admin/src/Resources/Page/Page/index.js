import NoteIcon from '@material-ui/icons/Note';

import { PagesList } from './PagesList';
import { PageShow } from './PageShow';
import { PageCreate } from './PageCreate';
import { PageEdit } from './PageEdit';

export default {
  options: {
    label: 'Articles',
  },
  list: PagesList,
  show: PageShow,
  create: PageCreate,
  edit: PageEdit,
  icon: NoteIcon,
};
