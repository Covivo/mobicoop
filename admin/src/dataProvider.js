import React from 'react';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import { fetchHydra as baseFetchHydra } from '@api-platform/admin';
import baseDataProvider from '@api-platform/admin/lib/hydra/dataProvider';
import { Redirect } from 'react-router-dom';
import { fetchUtils } from 'react-admin';
import { dataProviderAdapter } from './dataProviderAdapter';

const entrypoint = process.env.REACT_APP_API;
const token = global.localStorage.getItem('token');
const apiUrlCreateUSer = process.env.REACT_APP_API + process.env.REACT_APP_CREATE_USER;
const httpClient = fetchUtils.fetchJson;
const currentUser = global.localStorage.getItem('id');
const userRoles = [
  '/auth_items/1',
  '/auth_items/2',
  '/auth_items/4',
  '/auth_items/5',
  '/auth_items/6',
  '/auth_items/7',
  '/auth_items/8',
  '/auth_items/9',
  '/auth_items/10',
  '/auth_items/11',
  '/auth_items/12',
  '/auth_items/13',
  '/auth_items/171',
  '/auth_items/172',
];

const fetchHeaders = () => {
  return { Authorization: `Bearer ${global.localStorage.getItem('token')}` };
};

export const fetchHydra = (url, options = {}) =>
  baseFetchHydra(url, {
    ...options,
    headers: new global.Headers(fetchHeaders()),
  });

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

const dataProvider = baseDataProvider(entrypoint, fetchHydra, apiDocumentationParser);

export default dataProviderAdapter({
  ...dataProvider,
  create: (resource, params) => {
    if (resource !== 'users') {
      // fallback to the default implementation
      return dataProvider.create(resource, params);
    }
    const options = {
      headers: new global.Headers({ Accept: 'application/json' }),
    };

    options.headers.set('Authorization', `Bearer ${token}`);

    const newParams = { ...params };
    console.info(newParams);

    /* Rewrite roles for fit with api */
    const newRoles = [];
    newParams.data.fields.forEach(function (v) {
      const territory = v.territory;
      // There is many roles
      if (Array.isArray(v.roles)) {
        v.roles.forEach(function (r) {
          v != null ? newRoles.push({ authItem: r, territory }) : newRoles.push({ authItem: r });
        });
        // There is just 1 roles
      } else {
        v != null
          ? newRoles.push({ authItem: v.roles, territory })
          : newRoles.push({ authItem: v.roles });
      }
    });

    newParams.data.userAuthAssignments = newRoles;
    /* Rewrite roles for fit with api */

    /* Rewrite adresse for API */
    newParams.data.addresses = [];
    newParams.data.addresses[0] = newParams.data.address;
    newParams.data.addresses[0].home = true;

    /* Add custom fields fo fit with api */
    newParams.data.passwordSendType = 1;
    newParams.data.language = 'fr_FR';
    newParams.data.userDelegate = `/users/${currentUser}`;
    /* Add custom fields fo fit with api */

    return httpClient(`${apiUrlCreateUSer}`, {
      method: 'POST',
      body: JSON.stringify(newParams.data),
      headers: options.headers,
    }).then(({ json }) => ({
      data: { ...newParams.data, id: json.id },
    }));
  },
  getOne: (resource, params) => {
    if (resource !== 'users') {
      // fallback to the default implementation
      return dataProvider.getOne(resource, params);
    }

    const lid = params.id.search('users') === -1 ? `users/${params.id}` : params.id;

    return dataProvider.getOne('users', { id: lid }).then(({ data }) =>
      Promise.all(
        data.userAuthAssignments.map((element) =>
          dataProvider
            .getOne('userAuthAssignments', { id: element })
            .then(({ data }) => data)
            .catch((error) => {
              console.log('Erreur lors de la récupération des droits:', error);
            })
        )
      ).then(
        // We fill the array rolesTerritory with good format for admin
        (dataThen) => {
          dataThen.forEach((element) => {
            if (userRoles.includes(element.authItem)) data.rolesTerritory = dataThen;
          });
          return { data };
        }
      )
    );
  },
  getList: (resource, params) => {
    // Add a the custom filter : Admin, so we can have full control of resultats in API side
    return dataProvider.getList(
      resource === 'communities' ? 'communities/manage' : resource,
      params
    );
  },
  update: (resource, params) => {
    const newParams = { ...params };
    if (resource !== 'users') {
      // fallback to the default implementation
      return dataProvider.update(resource, newParams);
    }
    const options = {};
    options.headers = new global.Headers({ Accept: 'application/json' });
    options.headers.set('Authorization', `Bearer ${token}`);

    /* Rewrite roles for fit with api */
    const newRoles = [];
    if (newParams.data.fields != null) {
      newParams.data.fields.forEach(function (v) {
        const territory = v.territory;
        // There is many roles
        if (Array.isArray(v.roles)) {
          v.roles.forEach(function (r) {
            v != null ? newRoles.push({ authItem: r, territory }) : newRoles.push({ authItem: r });
          });
          // There is just 1 roles
        } else {
          v != null
            ? newRoles.push({ authItem: v.roles, territory })
            : newRoles.push({ authItem: v.roles });
        }
      });
    } else {
      const arrayRolesTerritories = newParams.data.rolesTerritory;

      arrayRolesTerritories.forEach((element) => {
        const territory = element.territory;
        const authItem = element.authItem;
        territory != null
          ? newRoles.push({ authItem: authItem, territory })
          : newRoles.push({ authItem: authItem });
      });
    }
    /* Rewrite roles for fit with api */
    newParams.data.userAuthAssignments = newRoles;

    return dataProvider.update('users', {
      id: newParams.id,
      data: newParams.data,
      previousData: newParams.data.previousData,
    });
  },
});
