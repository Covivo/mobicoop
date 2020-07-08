import HelpIcon from '@material-ui/icons/Help';
import CancelIcon from '@material-ui/icons/Cancel';
import HourglassFullIcon from '@material-ui/icons/HourglassFull';
import FindReplaceIcon from '@material-ui/icons/FindReplace';
import TrendingFlatIcon from '@material-ui/icons/TrendingFlat';
import BlockIcon from '@material-ui/icons/Block';
import CheckCircleIcon from '@material-ui/icons/CheckCircle';

const SOLIDARYASK_STATUS_ASKED = 0;
const SOLIDARYASK_STATUS_REFUSED = 1;
const SOLIDARYASK_STATUS_PENDING = 2;
const SOLIDARYASK_STATUS_LOOKFORSOLUTION = 3;
const SOLIDARYASK_STATUS_FOLLOWUP = 4;
const SOLIDARYASK_STATUS_CLOSED = 5;
const SOLIDARYASK_STATUS_ACCEPTED = 6;

export const solidaryAskStatusColors = {
  [SOLIDARYASK_STATUS_ASKED]: 'blue',
  [SOLIDARYASK_STATUS_REFUSED]: 'red',
  [SOLIDARYASK_STATUS_PENDING]: 'orange',
  [SOLIDARYASK_STATUS_LOOKFORSOLUTION]: 'brown',
  [SOLIDARYASK_STATUS_FOLLOWUP]: 'yellow',
  [SOLIDARYASK_STATUS_CLOSED]: 'grey',
  [SOLIDARYASK_STATUS_ACCEPTED]: 'green',
};

export const solidaryAskStatusIcons = {
  [SOLIDARYASK_STATUS_ASKED]: HelpIcon,
  [SOLIDARYASK_STATUS_REFUSED]: BlockIcon,
  [SOLIDARYASK_STATUS_PENDING]: HourglassFullIcon,
  [SOLIDARYASK_STATUS_LOOKFORSOLUTION]: FindReplaceIcon,
  [SOLIDARYASK_STATUS_FOLLOWUP]: TrendingFlatIcon,
  [SOLIDARYASK_STATUS_CLOSED]: CancelIcon,
  [SOLIDARYASK_STATUS_ACCEPTED]: CheckCircleIcon,
};

export const solidaryAskStatusLabels = {
  [SOLIDARYASK_STATUS_ASKED]: 'custom.solidaryask.status.asked',
  [SOLIDARYASK_STATUS_REFUSED]: 'custom.solidaryask.status.refused',
  [SOLIDARYASK_STATUS_PENDING]: 'custom.solidaryask.status.pending',
  [SOLIDARYASK_STATUS_LOOKFORSOLUTION]: 'custom.solidaryask.status.lookforresolution',
  [SOLIDARYASK_STATUS_FOLLOWUP]: 'custom.solidaryask.status.followup',
  [SOLIDARYASK_STATUS_CLOSED]: 'custom.solidaryask.status.closed',
  [SOLIDARYASK_STATUS_ACCEPTED]: 'custom.solidaryask.status.accepted',
};
