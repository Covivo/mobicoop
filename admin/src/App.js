import React from 'react';
import { Login, Resource, Admin } from 'react-admin';

import authProvider from './auth/authProvider';
import { createPermissionChecker, isAdmin } from './auth/permissions';
import Layout from './components/layout/Layout';
import i18nProvider from './i18n/translations';
import KibanaWidget from './components/dashboard/KibanaWidget';
import dataProvider from './dataProvider';
import theme from './theme';

import RightResource from './Resources/Right';
import RoleResource from './Resources/Role';
import CommunityResource from './Resources/Community';
import CommunityUserResource from './Resources/CommunityUser';
import UserResource from './Resources/User';
import CampaignResource from './Resources/Campaign';
import StructureProofResource from './Resources/Solidary/StructureProof';
import SolidaryResource from './Resources/Solidary/Solidary';
import SolidaryUsersBeneficiaryResource from './Resources/Solidary/SolidaryUserBeneficiary';
import SolidaryUsersVolunteerResource from './Resources/Solidary/SolidaryUserVolunteer';
import ArticleResource from './Resources/Article/Article';
import EventResource from './Resources/Event';
import SectionResource from './Resources/Article/Section';
import ParagraphResource from './Resources/Article/Paragraph';
import RelayPointResource from './Resources/RelayPoint/RelayPoint';
import RelayPointTypeResource from './Resources/RelayPoint/RelayPointType';
import SolidaryAnimationResource from './Resources/Solidary/SolidaryAnimation';
import SolidarySearchResource from './Resources/Solidary/SolidarySearch';
import StructureResource from './Resources/Solidary/Structure';

// Temporary disabled resources (Don't known why ?)
// import TerritoryResource from './Resources/Territory';
// import AddressResource from './Resources/Address';

const LoginPage = () => <Login backgroundImage={process.env.REACT_APP_THEME_BACKGROUND} />;
export default () => (
  <Admin
    dataProvider={dataProvider}
    authProvider={authProvider}
    loginPage={LoginPage}
    i18nProvider={i18nProvider}
    theme={theme}
    dashboard={KibanaWidget}
    layout={Layout}
  >
    {(permissions) => {
      const can = createPermissionChecker(permissions);

      return [
        <Resource name="users" {...(can('user_manage') ? UserResource : {})} />,
        <Resource name="communities" {...(can('community_manage') ? CommunityResource : {})} />,
        <Resource name="community_users" {...(can('user_manage') ? CommunityUserResource : {})} />,
        <Resource
          name={isAdmin() ? 'campaigns' : 'campaigns/owned'}
          {...(can('campaign_manage') ? CampaignResource : {})}
        />,
        <Resource name="events" {...(can('event_manage') ? EventResource : {})} />,
        <Resource name="articles" {...(can('article_manage') ? ArticleResource : {})} />,
        <Resource name="sections" {...(can('article_manage') ? SectionResource : {})} />,
        <Resource name="paragraphs" {...(can('article_manage') ? ParagraphResource : {})} />,
        <Resource name="relay_points" {...(can('relay_point_manage') ? RelayPointResource : {})} />,
        <Resource
          name="relay_point_types"
          {...(can('relay_point_manage') ? RelayPointTypeResource : {})}
        />,
        <Resource name="roles" {...(can('permission_manage') ? RoleResource : {})} />,
        <Resource name="rights" {...(can('permission_manage') ? RightResource : {})} />,
        <Resource
          name="solidary_beneficiaries"
          {...(can('solidary_manage') ? SolidaryUsersBeneficiaryResource : {})}
        />,
        <Resource
          name="solidary_volunteers"
          {...(can('solidary_volunteer_list') ? SolidaryUsersVolunteerResource : {})}
        />,
        <Resource name="solidaries" {...(can('solidary_manage') ? SolidaryResource : {})} />,
        <Resource
          name="structure_proofs"
          {...(can('user_manage') ? StructureProofResource : {})}
        />,
        <Resource
          name="solidary_animations"
          {...(can('solidary_manage') ? SolidaryAnimationResource : {})}
        />,
        <Resource
          name="solidary_searches"
          {...(can('solidary_manage') ? SolidarySearchResource : {})}
        />,
        <Resource name="structures" {...(can('user_manage') ? StructureResource : {})} />,
        <Resource name="addresses" />,
        <Resource name="images" />,
        <Resource name="needs" />,
        <Resource name="subjects" />,
        <Resource name="territories" />,
        <Resource name="solidary_users" />,
        <Resource name="solidary_contacts" />,
        <Resource name="actions" />,
        <Resource name="solidary_formal_requests" />,
        <Resource name="icons" />,
      ].filter((x) => x);
    }}
  </Admin>
);
