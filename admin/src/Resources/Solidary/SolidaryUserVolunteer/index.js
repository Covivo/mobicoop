import SolidaryUserVolunteerIcon from '@material-ui/icons/ContactPhone';

import { SolidaryUserVolunteerList } from './SolidaryUserVolunteerList';
import { SolidaryUserVolunteerEdit } from './SolidaryUserVolunteerEdit';

export default {
  options: {
    label: 'Transporteurs bénévoles',
  },
  list: SolidaryUserVolunteerList,
  edit: SolidaryUserVolunteerEdit,
  icon: SolidaryUserVolunteerIcon,
};
