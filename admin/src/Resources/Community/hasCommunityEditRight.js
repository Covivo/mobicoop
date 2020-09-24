import isAuthorized from '../../auth/permissions';

const hasCommunityEditRight = () =>
  isAuthorized('community_manage') || isAuthorized('community_manage_self');

const hasCommunityCreateRight = () => isAuthorized('community_create');

export { hasCommunityEditRight, hasCommunityCreateRight };
