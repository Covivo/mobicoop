import React, { Component } from 'react';

import { Admin, Login, Resource } from 'react-admin';
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

import { UserList, UserShow, UserCreate, UserEdit } from './Component/User/users';
import { CommunityList, CommunityShow, CommunityCreate, CommunityEdit } from './Component/Community/communities';
import { CommunityUserCreate, CommunityUserEdit } from './Component/Community/community_users';
import { RelayPointList, RelayPointShow, RelayPointCreate, RelayPointEdit } from './Component/RelayPoint/relaypoints';
import { RelayPointTypeList, RelayPointTypeShow, RelayPointTypeCreate, RelayPointTypeEdit } from './Component/RelayPoint/relaypointtypes';
import { ArticleList, ArticleShow, ArticleCreate, ArticleEdit } from './Component/Article/articles';
import { SectionShow, SectionCreate, SectionEdit } from './Component/Article/sections';
import { ParagraphCreate, ParagraphEdit } from './Component/Article/paragraphs';
import { TerritoryList, TerritoryShow, TerritoryCreate, TerritoryEdit } from './Component/Territory/territories';
import { RoleList, RoleShow, RoleCreate, RoleEdit } from './Component/Right/roles';
import { RightList, RightShow, RightCreate, RightEdit } from './Component/Right/rights';
import { AddressEdit } from './Component/Address/addresses';

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
                  isAuthorized("user_manage")         ? <Resource name="users" list={ UserList } show={ UserShow } create={ UserCreate } edit={ UserEdit } title="Utilisateurs" options={{ label: 'Utilisateurs' }} icon={PersonIcon} /> : null,
                  isAuthorized("community_manage")    ? <Resource name="communities" list={ CommunityList } show={ CommunityShow } create={ CommunityCreate } edit={ CommunityEdit } title="Communautés" options={{ label: 'Communautés' }} icon={PeopleIcon} /> : null,
                  isAuthorized("relay_point_manage")  ? <Resource name="relay_points" list={ RelayPointList } show={ RelayPointShow} create={ RelayPointCreate} edit={ RelayPointEdit} title="Points relais" options={{ label: 'Points relais' }} icon={LocalParkingIcon} /> : null,
                  isAuthorized("relay_point_manage")  ? <Resource name="relay_point_types" list={ RelayPointTypeList } show={ RelayPointTypeShow} create={ RelayPointTypeCreate} edit={ RelayPointTypeEdit} title="Types de points relais" options={{ label: 'Types de points relais' }} icon={LocalParkingIcon} /> : null,
                  isAuthorized("community_manage")    ? <Resource name="community_users" create={ CommunityUserCreate} edit={ CommunityUserEdit} /> : null,
                  isAuthorized("article_manage")      ? <Resource name="articles" list={ ArticleList } show={ ArticleShow} create={ ArticleCreate} edit={ ArticleEdit} title="Articles" options={{ label: 'Articles' }} icon={NoteIcon} /> : null,
                  isAuthorized("article_manage")      ? <Resource name="sections" show={ SectionShow} create={ SectionCreate} edit={ SectionEdit} /> : null,
                  isAuthorized("article_manage")      ? <Resource name="paragraphs" create={ ParagraphCreate} edit={ ParagraphEdit} /> : null,
                  isAuthorized("territory_manage")    ? <Resource name="territories" list={ TerritoryList} show={ TerritoryShow} create={ TerritoryCreate} edit={ TerritoryEdit} title="Territoires" options={{ label: 'Territoires' }} icon={MapIcon} /> : null,
                  <Resource name="geo_search" />,
                  <Resource name="addresses" edit={ AddressEdit} title="Adresses" options={{ label: 'Adresses' }} icon={MapIcon} />,
                  isAuthorized("permission_manage")   ? <Resource name="roles" list={ RoleList } show={ RoleShow} create={ RoleCreate} edit={ RoleEdit} title="Rôles" options={{ label: 'Rôles' }} icon={SupervisorAccountIcon} /> : null,
                  isAuthorized("permission_manage")   ? <Resource name="rights" list={ RightList } show={ RightShow} create={ RightCreate} edit={ RightEdit} title="Droits" options={{ label: 'Droits' }} icon={LockIcon} /> : null
                ];
            }
          }
          </Admin>
      )
  }
};
