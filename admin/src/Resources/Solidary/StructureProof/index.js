import SolidaryStructureProof from '@material-ui/icons/CheckBox';

import ListGuesser from '@api-platform/admin/lib/ListGuesser';
import ShowGuesser from '@api-platform/admin/lib/ShowGuesser';
import EditGuesser from '@api-platform/admin/lib/EditGuesser';
import CreateGuesser from '@api-platform/admin/lib/CreateGuesser';

export default {
  options: {
    label: 'Eligibilité',
  },
  list: ListGuesser,
  show: ShowGuesser,
  create: CreateGuesser,
  edit: EditGuesser,
  icon: SolidaryStructureProof,
};
