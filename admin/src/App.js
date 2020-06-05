import React from 'react';
import { Login, Resource, Admin } from 'react-admin';

import authProvider from './auth/authProvider';
import { createPermissionChecker } from './auth/permissions';
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

// Temporary disabled resources (Don't known why ?)
// import StructureResource from './Resources/Solidary/Structure';
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
        <Resource name="campaigns/owned" {...(can('campaign_manage') ? CampaignResource : {})} />,
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
          {...(can('solidary_manage') ? SolidaryUsersVolunteerResource : {})}
        />,
        <Resource name="solidaries" {...(can('solidary_manage') ? SolidaryResource : {})} />,
        <Resource
          name="structure_proofs"
          {...(can('user_manage') ? StructureProofResource : {})}
        />,
        // API Fail during "/structures" GET
        // @TODO: Fix API and remove the comment bellow
        // <Resource name="structures" {...(can('user_manage') ? StructureResource : {})} />,
        // These resources were commented on during my refacto, why ?
        // @TODO: Talk between us about that
        <Resource name="addresses" />,
        <Resource name="images" />,
        <Resource name="needs" />,
        <Resource name="subjects" />,
        <Resource name="territories" />,
      ].filter((x) => x);
    }}
  </Admin>
);
