import React, { Component } from 'react';

import { Admin, Resource } from 'react-admin';
import { Route, Redirect } from 'react-router-dom';
import { hydraClient, fetchHydra as baseFetchHydra  } from '@api-platform/admin';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';

import authProvider from './authProvider';

import { createMuiTheme } from '@material-ui/core/styles';

import PersonIcon from '@material-ui/icons/Person';
import PeopleIcon from '@material-ui/icons/People';
import LocalParkingIcon from '@material-ui/icons/LocalParking';
import SupervisorAccountIcon from '@material-ui/icons/SupervisorAccount';
import LockIcon from '@material-ui/icons/Lock';
import NoteIcon from '@material-ui/icons/Note';
import MapIcon from '@material-ui/icons/Map';

import frenchMessages from 'ra-language-french';

import { UserShow, UserEdit, UserCreate, UserList } from './Component/User/users';
import { CommunityShow, CommunityEdit, CommunityCreate, CommunityList } from './Component/Community/communities';
import { CommunityUserCreate, CommunityUserEdit } from './Component/Community/community_users';
import { RoleShow, RoleEdit, RoleCreate, RoleList } from './Component/Right/roles';
import { RightShow , RightList, RightEdit, RightCreate } from './Component/Right/rights';
import { RelayPointShow , RelayPointList, RelayPointEdit, RelayPointCreate } from './Component/RelayPoint/relaypoints';
import { RelayPointTypeShow , RelayPointTypeList, RelayPointTypeEdit, RelayPointTypeCreate } from './Component/RelayPoint/relaypointtypes';
import { ArticleShow, ArticleEdit, ArticleCreate, ArticleList } from './Component/Article/articles';
import { SectionCreate, SectionEdit } from './Component/Article/sections';
// import { ParagraphCreate, ParagraphEdit } from './Component/Article/paragraphs';
import {  TerritoryShow , TerritoryList, TerritoryEdit, TerritoryCreate } from './Component/Territory/territories';

require('dotenv').config();

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
      type: `${process.env.REACT_APP_THEME_TYPE}`
    },
});

const messages = {
  fr: frenchMessages,
}
const i18nProvider = locale => messages[locale];

// function to search for a given permission
// todo : refactor with authProvider function
function isAuthorized(action) {
  let permissions = JSON.parse(localStorage.getItem('permissions'));
  return permissions.hasOwnProperty(action);
}

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
                  api={ this.state.api }
                  locale="fr" i18nProvider={i18nProvider}
                  apiDocumentationParser={ apiDocumentationParser }
                  dataProvider= { dataProvider(this.state.api) }
                  theme={ theme }
                  authProvider={ authProvider }  
          >      
            {permissions => {
                return  [          
                  isAuthorized("user_manage") ? <Resource name="users" list={ UserList } create={ UserCreate } show={ UserShow } edit={ UserEdit } title="Utilisateurs" options={{ label: 'Utilisateurs' }} icon={PersonIcon} /> : null,
                  isAuthorized("community_manage") ? <Resource name="communities" list={ CommunityList } create={ CommunityCreate } show={ CommunityShow } edit={ CommunityEdit } title="Communautés" options={{ label: 'Communautés' }} icon={PeopleIcon} /> : null,
                  isAuthorized("permission_manage") ? <Resource name="roles" list={ RoleList } create={ RoleCreate} show={ RoleShow} edit={ RoleEdit} title="Rôles" options={{ label: 'Rôles' }} icon={SupervisorAccountIcon} /> : null,
                  isAuthorized("permission_manage") ? <Resource name="rights" list={ RightList } create={ RightCreate} show={ RightShow} edit={ RightEdit} title="Droits" options={{ label: 'Droits' }} icon={LockIcon} /> : null,
                  isAuthorized("relay_point_manage") ? <Resource name="relay_points" list={ RelayPointList } create={ RelayPointCreate} show={ RelayPointShow} edit={ RelayPointEdit} title="Points relais" options={{ label: 'Points relais' }} icon={LocalParkingIcon} /> : null,
                  isAuthorized("relay_point_manage") ? <Resource name="relay_point_types" list={ RelayPointTypeList } create={ RelayPointTypeCreate} show={ RelayPointTypeShow} edit={ RelayPointTypeEdit} title="Types de points relais" options={{ label: 'Types de points relais' }} icon={LocalParkingIcon} /> : null,
                  isAuthorized("community_manage") ? <Resource name="community_users" create={ CommunityUserCreate} edit={ CommunityUserEdit} /> : null,
                  isAuthorized("article_manage") ? <Resource name="articles" list={ ArticleList } create={ ArticleCreate} show={ ArticleShow} edit={ ArticleEdit} title="Articles" options={{ label: 'Articles' }} icon={NoteIcon} /> : null,
                  isAuthorized("article_manage") ? <Resource name="sections" create={ SectionCreate} edit={ SectionEdit} /> : null,
                  //{/* <Resource name="paragraphs" create={ ParagraphCreate} edit={ ParagraphEdit} /> */}
                  isAuthorized("territory_manage") ? <Resource name="territories" list={ TerritoryList} create={ TerritoryCreate} show={ TerritoryShow} edit={ TerritoryEdit} title="Territoires" options={{ label: 'Territoires' }} icon={MapIcon} /> : null,
                  <Resource name="geo_search" />,
                  <Resource name="addresses" />
                ];
            }
          }
          </Admin>
      )
  }
};
