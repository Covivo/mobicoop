import React from 'react';
import { HydraAdmin } from '@api-platform/admin';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import { dataProvider as baseDataProvider, fetchHydra as baseFetchHydra  } from '@api-platform/admin';
import authProvider from './authProvider';
import { Redirect } from 'react-router-dom';

import { Login, Resource } from 'react-admin';

import { createMuiTheme } from '@material-ui/core/styles';
import frenchMessages from 'ra-language-french';
import MapIcon from '@material-ui/icons/Map';

import users from './Component/User/index';
import articles from './Component/Article/Article/index';
import sections from './Component/Article/Section/index';
import paragraphs from './Component/Article/Paragraph/index';
import communities from './Component/Community/Community/index';
import community_users from './Component/Community/CommunityUser/index';
import relay_points from './Component/RelayPoint/RelayPoint/index';
import relay_point_types from './Component/RelayPoint/RelayPointType/index';
import roles from './Component/Right/Role/index';
import rights from './Component/Right/Right/index';
import territories from './Component/Territory/index';
import events from './Component/Event/index';
import { AddressEdit } from './Component/Address/addresses';

import isAuthorized from './Component/Utilities/authorization';

require('dotenv').config();

const MyLoginPage = () => <Login backgroundImage={process.env.REACT_APP_THEME_BACKGROUND} />;

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
      }
    },
});

const messages = {
  fr: frenchMessages,
}
const i18nProvider = locale => messages[locale];

const entrypoint = process.env.REACT_APP_API;

// use this if the next code doesn't work... and remove the () after fetchHeaders on apiDocumentationParser declaration...
// const fetchHeaders = {'Authorization': `Bearer ${localStorage.getItem('token')}`};
// const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
//     ...options,
//     headers: new Headers(fetchHeaders),
// });

const fetchHeaders = function () {
  return {'Authorization': `Bearer ${localStorage.getItem('token')}`};
};
const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
    ...options,
    headers: new Headers(fetchHeaders()),
});

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
const dataProvider = baseDataProvider(entrypoint, fetchHydra, apiDocumentationParser);

// todo : create a default resource that leads to the login page
export default props => (
    <HydraAdmin
        apiDocumentationParser={ apiDocumentationParser }
        dataProvider={ dataProvider }
        authProvider={ authProvider }
        entrypoint={ entrypoint }
        loginPage={ MyLoginPage }
        locale="fr" i18nProvider={ i18nProvider }
        theme={ theme }
    >
      {permissions => {
        return  [          
          <Resource name={'users'} {...users} />,
          isAuthorized("article_manage")      ? <Resource name={'articles'} {...articles} /> : null,
          isAuthorized("article_manage")      ? <Resource name={'sections'} {...sections} /> : null,
          isAuthorized("article_manage")      ? <Resource name={'paragraphs'} {...paragraphs} /> : null,
          isAuthorized("community_manage")    ? <Resource name={'communities'} {...communities} /> : null,
          isAuthorized("community_manage")    ? <Resource name={'community_users'} {...community_users} /> : null,
          isAuthorized("relay_point_manage")  ? <Resource name={'relay_points'} {...relay_points} /> : null,
          isAuthorized("relay_point_manage")  ? <Resource name={'relay_point_types'} {...relay_point_types} /> : null,
          isAuthorized("permission_manage")   ? <Resource name={'roles'} {...roles} /> : null,
          isAuthorized("permission_manage")   ? <Resource name={'rights'} {...rights} /> : null,
          isAuthorized("territory_manage")    ? <Resource name={'territories'} {...territories} /> : null,
          isAuthorized("event_manage")        ? <Resource name={'events'} {...events} /> : null,
          <Resource name="addresses" edit={ AddressEdit} title="Adresses" options={{ label: 'Adresses' }} icon={MapIcon} />,
          <Resource name="images" />
        ];
    }
      }
    </HydraAdmin>
);