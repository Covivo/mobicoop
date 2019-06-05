import React, { Component } from 'react';

import { fetchUtils,Admin, Resource } from 'react-admin';

import mobicoopDataProvider from './mobicoopDataProvider';
import authProvider from './authProvider';

import { createMuiTheme } from '@material-ui/core/styles';
import { cyan, lightBlue, teal } from '@material-ui/core/colors';
import PersonIcon from '@material-ui/icons/Person';
import PeopleIcon from '@material-ui/icons/People';
import LocalParkingIcon from '@material-ui/icons/LocalParking';
import SupervisorAccountIcon from '@material-ui/icons/SupervisorAccount';
import LockIcon from '@material-ui/icons/Lock';
import NoteIcon from '@material-ui/icons/Note';

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

const theme = createMuiTheme({
    palette: {
      primary: cyan,
      secondary: lightBlue,
      error: teal,
      // Used by `getContrastText()` to maximize the contrast between the background and
      // the text.
      contrastThreshold: 3,
      // Used to shift a color's luminance by approximately
      // two indexes within its tonal palette.
      // E.g., shift from Red 500 to Red 300 or Red 700.
      tonalOffset: 0.2,
      type: 'light'
    },
});

const messages = {
  fr: frenchMessages,
}
const i18nProvider = locale => messages[locale];

require('dotenv').config();

const entrypoint = process.env.REACT_APP_API;

const httpClient = (url, options = {}) => {
  if (!options.headers) {
       options.headers = new Headers({ Accept: 'application/ld+json' });
  }
  const token = localStorage.getItem('token');
  options.headers.set('Authorization', `Bearer ${token}`);
  return fetchUtils.fetchJson(url, options);
}

const dataProvider = mobicoopDataProvider(entrypoint, httpClient);

export default class extends Component {

  render() {
      return (
          <Admin 
                  locale="fr" i18nProvider={i18nProvider}
                  dataProvider= { dataProvider }
                  theme={ theme }
                  authProvider={ authProvider }  
          >                
              <Resource name="users" list={ UserList } create={ UserCreate } show={ UserShow } edit={ UserEdit } title="Utilisateurs" options={{ label: 'Utilisateurs' }} icon={PersonIcon} />
              <Resource name="communities" list={ CommunityList } create={ CommunityCreate } show={ CommunityShow } edit={ CommunityEdit } title="Communautés" options={{ label: 'Communautés' }} icon={PeopleIcon} />
              <Resource name="roles" list={ RoleList } create={ RoleCreate} show={ RoleShow} edit={ RoleEdit} title="Rôles" options={{ label: 'Rôles' }} icon={SupervisorAccountIcon} />
              <Resource name="rights" list={ RightList } create={ RightCreate} show={ RightShow} edit={ RightEdit} title="Droits" options={{ label: 'Droits' }} icon={LockIcon} />
              <Resource name="relay_points" list={ RelayPointList } create={ RelayPointCreate} show={ RelayPointShow} edit={ RelayPointEdit} title="Points relais" options={{ label: 'Points relais' }} icon={LocalParkingIcon} />
              <Resource name="relay_point_types" list={ RelayPointTypeList } create={ RelayPointTypeCreate} show={ RelayPointTypeShow} edit={ RelayPointTypeEdit} title="Types de points relais" options={{ label: 'Types de points relais' }} icon={LocalParkingIcon} />
              <Resource name="community_users" create={ CommunityUserCreate} edit={ CommunityUserEdit} />
              <Resource name="articles" list={ ArticleList } create={ ArticleCreate} show={ ArticleShow} edit={ ArticleEdit} title="Articles" options={{ label: 'Articles' }} icon={NoteIcon} />
              <Resource name="sections" create={ SectionCreate} edit={ SectionEdit} />
              {/* <Resource name="paragraphs" create={ ParagraphCreate} edit={ ParagraphEdit} /> */}
              <Resource name="geo_search" />
              <Resource name="addresses" />
          </Admin>
      )
  }
};
