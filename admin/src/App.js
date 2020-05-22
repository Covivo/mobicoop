import React from 'react';
import { Login, Resource } from 'react-admin';
import { HydraAdmin, ResourceGuesser } from '@api-platform/admin';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import { Redirect } from 'react-router-dom';

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
import StructureResource from './Resources/Solidary/Structure';
import ArticleResource from './Resources/Article/Article';
import EventResource from './Resources/Event';
import SectionResource from './Resources/Article/Section';
import ParagraphResource from './Resources/Article/Paragraph';
import RelayPointResource from './Resources/RelayPoint/RelayPoint';
import RelayPointTypeResource from './Resources/RelayPoint/RelayPointType';

// Temporary disabled resources (Don't known why ?)
// import TerritoryResource from './Resources/Territory';
// import AddressResource from './Resources/Address';

const LoginPage = () => <Login backgroundImage={process.env.REACT_APP_THEME_BACKGROUND} />;
const entrypoint = process.env.REACT_APP_API;

const fetchHeaders = () => {
  return { Authorization: `Bearer ${global.localStorage.getItem('token')}` };
};

const apiDocumentationParser = (entrypoint) =>
  parseHydraDocumentation(entrypoint, { headers: new global.Headers(fetchHeaders()) }).then(
    ({ api }) => ({ api }),
    (result) => {
      switch (result.status) {
        case 401:
          return Promise.resolve({
            api: result.api,
            customRoutes: [
              {
                props: {
                  path: '/',
                  render: () => <Redirect to="/login" />,
                },
              },
            ],
          });

        default:
          return Promise.reject(result);
      }
    }
  );

export default () => (
  <HydraAdmin
    apiDocumentationParser={apiDocumentationParser}
    dataProvider={dataProvider}
    authProvider={authProvider}
    entrypoint={entrypoint}
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
        isAuthorized('community_manage') && (
          <Resource name="community_users" {...CommunityUserResource} />
        ),
        isAuthorized('campaign_manage') && (
          <Resource name="campaigns/owned" {...CampaignResource} />
        ),
        isAuthorized('event_manage') && <ResourceGuesser name="events" {...EventResource} />,
        isAuthorized('article_manage') && <Resource name="articles" {...ArticleResource} />,
        isAuthorized('article_manage') && <Resource name="sections" {...SectionResource} />,
        isAuthorized('article_manage') && <Resource name="paragraphs" {...ParagraphResource} />,
        isAuthorized('relay_point_manage') && (
          <Resource names="relay_points" {...RelayPointResource} />
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
        //isAuthorized('user_manage') && <Resource name="structures" {...StructureResource} />,
        isAuthorized('user_manage') && (
          <Resource name="structure_proofs" {...StructureProofResource} />
        ),
        // These resources were commented on during my refacto, why ?
        // @TODO: Talk between us about that
        // <Resource name="addresses" {...AddressResource} />,
        // <Resource name="images" />,
        // <Resource name="permissions/roles" />,
        // <Resource name="territories" {...TerritoryResource} />,
      ].filter((x) => x)
    }
  </HydraAdmin>
);
