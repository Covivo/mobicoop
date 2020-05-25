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

const fetchHeaders = () => {
  return { Authorization: `Bearer ${global.localStorage.getItem('token')}` };
};

const fetchHydra = (url, options = {}) =>
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
    const options = {};
    if (!options.headers) {
      options.headers = new global.Headers({ Accept: 'application/json' });
    }
    options.headers.set('Authorization', `Bearer ${token}`);

    /* Rewrite roles for fit with api */
    let newRoles = [];
    const newParams = { ...params };

    newParams.data.fields.forEach(function (v) {
      var territory = v.territory;
      v.roles.forEach(function (r) {
        v != null ? newRoles.push({ authItem: r, territory }) : newRoles.push({ authItem: r });
      });
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

    var lid = params.id.search('users') === -1 ? 'users/' + params.id : params.id;

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
          data.rolesTerritory = dataThen.reduce((acc, val) => {
            var territory = val.territory == null ? 'null' : val.territory;

            if (!acc[territory]) {
              acc[territory] = [];
            }
            acc[territory].push(val.authItem);
            return acc;
          }, {});
          return { data };
        }
      )
    );
  },
  getList: (resource, params) => {
    if (resource === 'communities') {
      // Add a the custom filter : Admin, so we can have full control of resultats in API side
      return dataProvider.getList(`${resource}/accesFromAdminReact`, params);
    }

    return dataProvider.getList(resource, params);
  },
  update: (resource, params) => {
    const newParams = { ...params };

    if (resource !== 'users') {
      return dataProvider.update(resource, newParams);
    }

    const options = {};
    options.headers = new global.Headers({ Accept: 'application/json' });
    options.headers.set('Authorization', `Bearer ${token}`);

    /* Rewrite roles for fit with api */
    const newRoles = [];
    if (newParams.data.fields != null) {
      newParams.data.fields.forEach(function (v) {
        var territory = v.territory;
        v.roles.forEach(function (r) {
          v != null
            ? newRoles.push({ authItem: r, territory: territory })
            : newRoles.push({ authItem: r });
        });
      });
    } else {
      for (const territory in newParams.data.rolesTerritory) {
        for (const r in newParams.data.rolesTerritory[territory]) {
          const role = newParams.data.rolesTerritory[territory][r];
          territory != null
            ? newRoles.push({ authItem: role, territory: territory })
            : newRoles.push({ authItem: role });
        }
      }
    }

    newParams.data.userAuthAssignments = newRoles;
    /* Rewrite roles for fit with api */
    return dataProvider.update('users', {
      id: newParams.data.originId,
      data: newParams.data,
      previousData: newParams.data.previousData,
    });
  },
});
