import SolidaryUserVolunteerIcon from '@material-ui/icons/ContactPhone';

import ListGuesser from '@api-platform/admin/lib/ListGuesser';
import ShowGuesser from '@api-platform/admin/lib/ShowGuesser';

export default {
  options: {
    label: 'Bénévoles',
  },
  list: ListGuesser,
  show: ShowGuesser,
  icon: SolidaryUserVolunteerIcon,
};
