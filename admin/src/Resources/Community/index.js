import PeopleIcon from '@material-ui/icons/People';
import isAuthorized from '../../auth/permissions';
import { CommunityList } from './CommunityList';
import { CommunityShow } from './CommunityShow';
import { CommunityCreate } from './CommunityCreate';
import { CommunityEdit } from './CommunityEdit';

const hasCommunityEditRight =
  isAuthorized('community_manage') || isAuthorized('community_manage_self');

export default {
  options: {
    label: 'Communautés',
  },
  list: CommunityList, // API should return a full list ("community_list" permission), or only my community (default)
  show: CommunityShow,
  create: isAuthorized('community_create') && CommunityCreate,
  edit: hasCommunityEditRight && CommunityEdit,
  icon: PeopleIcon,
};
