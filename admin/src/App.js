import React from 'react';
import { Login, Resource, Admin } from 'react-admin';

import authProvider from './auth/authProvider';
import isAuthorized from './auth/permissions';
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
    {() =>
      [
        <Resource name="users" {...(isAuthorized('user_manage') ? UserResource : {})} />,
        isAuthorized('community_manage') && <Resource name="communities" {...CommunityResource} />,
        isAuthorized('user_manage') && (
          <Resource name="community_users" {...CommunityUserResource} />
        ),
        isAuthorized('campaign_manage') && (
          <Resource name="campaigns/owned" {...CampaignResource} />
        ),
        isAuthorized('event_manage') && <Resource name="events" {...EventResource} />,
        isAuthorized('article_manage') && <Resource name="articles" {...ArticleResource} />,
        isAuthorized('article_manage') && <Resource name="sections" {...SectionResource} />,
        isAuthorized('article_manage') && <Resource name="paragraphs" {...ParagraphResource} />,
        isAuthorized('relay_point_manage') && (
          <Resource name="relay_points" {...RelayPointResource} />
        ),
        isAuthorized('relay_point_manage') && (
          <Resource name="relay_point_types" {...RelayPointTypeResource} />
        ),
        isAuthorized('permission_manage') && <Resource name="roles" {...RoleResource} />,
        isAuthorized('permission_manage') && <Resource name="rights" {...RightResource} />,
        isAuthorized('solidary_manage') && (
          <Resource name="solidary_beneficiaries" {...SolidaryUsersBeneficiaryResource} />
        ),
        isAuthorized('solidary_manage') && (
          <Resource name="solidary_volunteers" {...SolidaryUsersVolunteerResource} />
        ),
        isAuthorized('solidary_manage') && <Resource name="solidaries" {...SolidaryResource} />,
        // API Fail during "/structures" GET
        // @TODO: Fix API and remove the comment bellow
        // isAuthorized('user_manage') && <Resource name="structures" {...StructureResource} />,
        isAuthorized('user_manage') && (
          <Resource name="structure_proofs" {...StructureProofResource} />
        ),
        // These resources were commented on during my refacto, why ?
        // @TODO: Talk between us about that
        <Resource name="addresses" />,
        // <Resource name="images" />,
        <Resource name="territories" />,
      ].filter((x) => x)
    }
  </Admin>
);
