import React, { Component } from 'react';

import { Admin, Login, Resource } from 'react-admin';
import { Route, Redirect } from 'react-router-dom';
import { hydraClient, fetchHydra as baseFetchHydra  } from '@api-platform/admin';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';

import authProvider from './authProvider';

import { createMuiTheme } from '@material-ui/core/styles';

import MapIcon from '@material-ui/icons/Map';

import frenchMessages from 'ra-language-french';

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

const fetchHeaders = function () {
  return {'Authorization': `Bearer ${localStorage.getItem('token')}`};
};
const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
    ...options,
    headers: new Headers(fetchHeaders()),
});
const dataProvider = api => hydraClient(api, fetchHydra);
const apiDocumentationParser = entrypoint =>
  parseHydraDocumentation(entrypoint, {
    headers: new Headers(fetchHeaders()),
  }).then(
    ({ api }) => ({ api }),
    result => {
      const { api, status } = result;

      if (status === 401) {      
        return Promise.resolve({
          api,
          status,
          customRoutes: [
            <Route path="/" render={() => <Redirect to="/login" />} />,
          ],
        });
      }

      return Promise.reject(result);
    }
  );

export default class extends Component {
  state = { api: null };

  componentDidMount() {
    apiDocumentationParser(entrypoint).then(({ api }) => {
      this.setState({ api });
    }).catch((e) => {
      console.log(e);
    });
  }

  render() {
      if (null === this.state.api) return <div>Loading...</div>;
      return (
          <Admin 
                  loginPage={MyLoginPage}
                  api={ this.state.api }
                  locale="fr" i18nProvider={i18nProvider}
                  apiDocumentationParser={ apiDocumentationParser }
                  dataProvider= { dataProvider(this.state.api) }
                  theme={ theme }
                  authProvider={ authProvider }  
          >      
            {permissions => {
                return  [          
                  isAuthorized("user_manage")         ? <Resource name={'users'} {...users} /> : null,
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
                  <Resource name="geo_search" />,
                  <Resource name="community_users" />,
                  <Resource name="addresses" edit={ AddressEdit} title="Adresses" options={{ label: 'Adresses' }} icon={MapIcon} />,
                  <Resource name="images" />
                ];
            }
          }
          </Admin>
      )
  }
};
