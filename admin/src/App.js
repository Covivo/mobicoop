import React from 'react';
import { HydraAdmin } from '@api-platform/admin';

import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import authProvider from './Auth/authProvider';
import isAuthorized from './Auth/permissions'
import { Redirect } from 'react-router-dom';

import { Login, Resource } from 'react-admin';

import { createMuiTheme } from '@material-ui/core/styles';
import i18nProviderTranslations  from './Component/Utilities/translations'
import MyLayout from './MyLayout'

import users from './Component/User/index';
import articles from './Component/Article/Article/index';
import sections from './Component/Article/Section/index';
import paragraphs from './Component/Article/Paragraph/index';
import communities from './Component/Community/Community/index';
import community_users from './Component/Community/CommunityUser/index';
import relay_points from './Component/RelayPoint/RelayPoint/index';
import roles from './Component/Right/Role/index';
import rights from './Component/Right/Right/index';
import events from './Component/Event/index';
import KibanaWidget from './Component/Dashboard/KibanaWidget'
import {ResourceGuesser, } from '@api-platform/admin/lib';
import myDataProvider from "./Component/Utilities/extendProviders";
import campaigns from "./Component/Campaigns/index";

import solidaries from './Component/Solidary/Solidary'
import solidary_users_beneficiary from './Component/Solidary/SolidaryUserBeneficiary'
import solidary_users_volunteer from './Component/Solidary/SolidaryUserVolunteer'
import structures from './Component/Solidary/Structure'
import structure_proofs from './Component/Solidary/StructureProof'

require('dotenv').config();

const MyLoginPage = () => <Login backgroundImage={process.env.REACT_APP_THEME_BACKGROUND} />;
const entrypoint = process.env.REACT_APP_API;

const theme = createMuiTheme({
    palette: {
      primary: {
        main: `#${process.env.REACT_APP_THEME_PRIMARY_COLOR}`,
      },
      secondary: {
        main: `#${process.env.REACT_APP_THEME_SECONDARY_COLOR}`,
      },
      error: {
        main: `#${process.env.REACT_APP_THEME_ERROR_COLOR}`,
      },
      contrastThreshold: process.env.REACT_APP_THEME_CONTRAST_THRESHOLD,
      tonalOffset: process.env.REACT_APP_THEME_TONAL_OFFSET,
      type: `${process.env.REACT_APP_THEME_TYPE}`,
      background: {
        paper: `#${process.env.REACT_APP_THEME_BACKGROUND_PAPER_COLOR}`,
        default: `#${process.env.REACT_APP_THEME_BACKGROUND_DEFAULT_COLOR}`
      },
    },
    /*
    overrides: {
      MuiCardContent : {
        root : { display:"flex", flexWrap:"wrap", justifyContent:"space-between"}
      },
    }
    */
});

const i18nProvider = i18nProviderTranslations;

const fetchHeaders = function () {
    return {'Authorization': `Bearer ${localStorage.getItem('token')}`};
};

const apiDocumentationParser = entrypoint => parseHydraDocumentation(entrypoint, { headers: new Headers(fetchHeaders()) })
    .then(
        ({ api }) => ({api}),
        (result) => {
            switch (result.status) {
                case 401:
                    return Promise.resolve({
                        api: result.api,
                        customRoutes: [{
                            props: {
                                path: '/',
                                render: () => <Redirect to={`/login`}/>,
                            },
                        }],
                    });

                default:
                    return Promise.reject(result);
            }
        },
    );
// use this if the next code doesn't work... and remove the () after fetchHeaders on apiDocumentationParser declaration...
// const fetchHeaders = {'Authorization': `Bearer ${localStorage.getItem('token')}`};
// const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
//     ...options,
//     headers: new Headers(fetchHeaders),
// });



// todo : create a default resource that leads to the login page
export default props => (
    <HydraAdmin
        apiDocumentationParser={ apiDocumentationParser }
        dataProvider={ myDataProvider }
        authProvider={ authProvider }
        entrypoint={ entrypoint }
        loginPage={ MyLoginPage }
        i18nProvider={ i18nProvider }
        theme={ theme }
        dashboard={KibanaWidget}
        layout={MyLayout}
    >
      {permissions => {
        return  [
          
          isAuthorized("user_manage")         ? <Resource name={'users'} {...users} /> : <Resource name={'users'}  />,
          isAuthorized("community_manage")    ? <Resource name={'communities'} {...communities} /> : null,
          isAuthorized("community_manage")    ? <Resource name={'community_users'} {...community_users} /> : null,
          isAuthorized("campaign_manage")     ? <Resource name={'campaigns/owned'} {...campaigns} /> : null,

          isAuthorized("event_manage")        ? <Resource name={'events'} {...events} /> : null,

          isAuthorized("article_manage")      ? <Resource name={'articles'} {...articles} /> : null,
          isAuthorized("article_manage")      ? <Resource name={'sections'} {...sections} /> : null,
          isAuthorized("article_manage")      ? <Resource name={'paragraphs'} {...paragraphs} /> : null,

          isAuthorized("relay_point_manage")  ? <Resource name={'relay_points'} {...relay_points} /> : null,
          isAuthorized("relay_point_manage")  ? <Resource name={'relay_point_types'} />  : null,
          isAuthorized("permission_manage")   ? <Resource name={'roles'} {...roles} /> : null,
          isAuthorized("permission_manage")   ? <Resource name={'rights'} {...rights} /> : null,

          isAuthorized("solidary_manage")     ? <Resource name="solidary_users" {...solidary_users_beneficiary} /> : null ,
          isAuthorized("solidary_manage")     ? <Resource name="solidary_users" {...solidary_users_volunteer} /> : null ,
          isAuthorized("solidary_manage")     ? <Resource name="solidaries" {...solidaries} /> : null ,

          isAuthorized("user_manage")         ? <Resource name="structures" {...structures} /> : null ,
          isAuthorized("user_manage")         ? <Resource name="structure_proofs" {...structure_proofs} /> : null ,

          <Resource name="addresses" />,
          <Resource name="images" />,
          <Resource name="permissions/roles" />,
          <Resource name="territories" />,
          
         ];
     }
       }
     </HydraAdmin>
 );
