import { AUTH_LOGIN, AUTH_LOGOUT, AUTH_ERROR, AUTH_CHECK, fetchUtils } from 'react-admin';
import decodeJwt from 'jwt-decode';
import { AUTH_GET_PERMISSIONS } from 'ra-core';
import isAuthorized from './permissions';

require('dotenv').config();

// Authentication token URI
const authenticationTokenUri = process.env.REACT_APP_API_LOGIN;

export default (type, params) => {
  switch (type) {
    case AUTH_LOGIN:
      const { username, password } = params;

      const request = new global.Request(authenticationTokenUri, {
        method: 'POST',
        body: JSON.stringify({ username, password }),
        headers: new global.Headers({ 'Content-Type': 'application/json' }),
      });

      return global
        .fetch(request)
        .then((response) => {
          if (response.status < 200 || response.status >= 300) throw new Error(response.statusText);
          return response.json();
        })
        .then(({ token }) => {
          const decodedToken = decodeJwt(token);
          if (!decodedToken.admin) throw new Error('Unauthorized');
          global.localStorage.setItem('token', token);
          global.localStorage.setItem('roles', decodedToken.roles);
          global.localStorage.setItem('id', decodedToken.id);
          const options = {};

          const apiPermissions = process.env.REACT_APP_API + '/permissions';
          const httpClient = fetchUtils.fetchJson;
          if (!options.headers) {
            options.headers = new global.Headers({ Accept: 'application/json' });
          }
          options.headers.set('Authorization', `Bearer ${global.localStorage.token}`);

          return httpClient(apiPermissions, {
            method: 'GET',
            headers: options.headers,
          }).then((retour) => {
            if ((retour.status = '200')) {
              return global.localStorage.setItem('permission', retour.body);
            }
          });
        });

    case AUTH_LOGOUT:
      global.localStorage.removeItem('token');
      global.localStorage.removeItem('roles');
      global.localStorage.removeItem('id');
      global.localStorage.removeItem('permission');
      return Promise.resolve();

    case AUTH_ERROR:
      if (params.response && (401 === params.response.status || 403 === params.response.status)) {
        global.localStorage.removeItem('token');
        global.localStorage.removeItem('roles');
        global.localStorage.removeItem('id');
        global.localStorage.removeItem('permission');
        return Promise.reject();
      }

      console.log('AUTH_ERROR');
      return Promise.resolve();

    case AUTH_CHECK:
      return global.localStorage.getItem('token')
        ? Promise.resolve()
        : Promise.reject({ redirectTo: '/login' });

    case AUTH_GET_PERMISSIONS:
      let permission;
      if (params && params.location) {
        switch (params.location) {
          // create a use case for each resource route
          // can be divided if permission must be more granular (eg. permissions on field level)
          case '/users':
            permission = isAuthorized('user_manage');
            break;
          case '/communities':
            permission = isAuthorized('community_manage');
            break;
          case '/events':
            permission = isAuthorized('event_manage');
            break;
          case '/campaigns':
            permission = isAuthorized('campaign_manage');
            break;
          case '/roles':
            permission = isAuthorized('permission_manage');
            break;
          case '/rights':
            permission = isAuthorized('permission_manage');
            break;
          case '/relay_points':
            permission = isAuthorized('relay_point_manage');
            break;
          case '/relay_point_types':
            permission = isAuthorized('relay_point_manage');
            break;
          case '/community_users':
            permission = isAuthorized('community_manage');
            break;
          case '/articles':
            permission = isAuthorized('article_manage');
            break;
          case '/sections':
            permission = isAuthorized('article_manage');
            break;
          case '/paragraphs':
            permission = isAuthorized('article_manage');
            break;
          case '/territories':
            permission = isAuthorized('territory_manage');
            break;
          default:
            break;
        }
      } else {
        permission = true;
      }
      return permission ? Promise.resolve(permission) : Promise.reject();

    default:
      return Promise.resolve();
  }
};
