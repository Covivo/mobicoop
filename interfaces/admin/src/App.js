import React, { Component } from 'react';
import { Admin, Resource } from 'react-admin';
import { Route, Redirect } from 'react-router-dom';
import { hydraClient, fetchHydra as baseFetchHydra  } from '@api-platform/admin';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import authProvider from './authProvider';

import { createMuiTheme } from '@material-ui/core/styles';
import { cyan, lightBlue, teal } from '@material-ui/core/colors';

import PersonIcon from '@material-ui/icons/Person';
import PeopleIcon from '@material-ui/icons/People';
import SupervisorAccountIcon from '@material-ui/icons/SupervisorAccount';
import LockIcon from '@material-ui/icons/Lock';

import frenchMessages from 'ra-language-french';

import { UserShow, UserEdit, UserCreate, UserList } from './Component/User/users';
import { CommunityShow, CommunityEdit, CommunityCreate, CommunityList } from './Component/Community/communities';
import { CommunityUserCreate } from './Component/Community/community_users';
import { RoleShow, RoleEdit, RoleCreate, RoleList } from './Component/Right/roles';
import { RightShow , RightList, RightEdit, RightCreate } from './Component/Right/rights';


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
const fetchHeaders = {'Authorization': `Bearer ${localStorage.getItem('token')}`};
const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
    ...options,
    headers: new Headers(fetchHeaders),
});
const dataProvider = api => hydraClient(api, fetchHydra);
const apiDocumentationParser = entrypoint =>
  parseHydraDocumentation(entrypoint, {
    headers: new Headers(fetchHeaders),
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
          <Admin api={ this.state.api }
                  locale="fr" i18nProvider={i18nProvider}
                  apiDocumentationParser={ apiDocumentationParser }
                  dataProvider= { dataProvider(this.state.api) }
                  theme={ theme }
                  // appLayout={ Layout }
                  authProvider={ authProvider }          
          >                
              <Resource name="users" list={ UserList } create={ UserCreate } show={ UserShow } edit={ UserEdit } title="Utilisateurs" options={{ label: 'Utilisateurs' }} icon={PersonIcon} />
              <Resource name="communities" list={ CommunityList } create={ CommunityCreate } show={ CommunityShow } edit={ CommunityEdit } title="Communautés" options={{ label: 'Communautés' }} icon={PeopleIcon} />
              <Resource name="roles" list={ RoleList } create={ RoleCreate} show={ RoleShow} edit={ RoleEdit} title="Rôles" options={{ label: 'Rôles' }} icon={SupervisorAccountIcon} />
              <Resource name="rights" list={ RightList } create={ RightCreate} show={ RightShow} edit={ RightEdit} title="Droits" options={{ label: 'Droits' }} icon={LockIcon} />
              <Resource name="community_users" create={ CommunityUserCreate} />
          </Admin>
      )
  }
}