import { CREATE, DELETE, GET_LIST, GET_MANY_REFERENCE, GET_ONE, UPDATE } from 'react-admin';
import isPlainObject from 'lodash.isplainobject';

/**
 * This file acts as a simplified version of the API Platform dataProvider
 * https://github.com/api-platform/admin/blob/master/src/hydra/dataProvider.js
 * It doesn't use the api schema file and only convert hydra to RA format
 */

/**
 * Simple memory cache to speedup requests
 */
const cache = new Map();

/**
 * Transform object with deep fields to a flat map to be handled by the API
 * Eg: { name: "foo", address: { @id: "/addresses/12" } }
 * Will be transformed to: { name: "foo", address: "/addresses/12" }
 *
 * Other Eg: { name: "foo", addresses: [{ @id: "/addresses/12" }] }
 * Will be transformed to: { name: "foo", addresses: ["/addresses/12"] }
 */
const stringifyDeepObjects = (obj) =>
  Object.keys(obj).reduce((agg, key) => {
    if (isPlainObject(obj[key]) && obj[key]['@id']) {
      agg[key] = obj[key]['@id'];
    } else if (
      Array.isArray(obj[key]) &&
      obj[key].length &&
      isPlainObject(obj[key][0]) &&
      obj[key][0]['@id']
    ) {
      agg[key] = obj[key].map((object) => object['@id']);
    } else if (isPlainObject(obj[key])) {
      agg[key] = stringifyDeepObjects(obj[key]);
    } else {
      agg[key] = obj[key];
    }

    return agg;
  }, {});

const createReactAdminToHydraRequestConverter = (entrypoint) => (type, resource, params) => {
  const entrypointUrl = new URL(entrypoint, window.location.href);
  const collectionUrl = new URL(`${entrypoint}/${resource}`, entrypointUrl);
  const itemUrl = new URL(params.id, entrypointUrl);

  switch (type) {
    case CREATE:
      return Promise.resolve({
        options: {
          body: JSON.stringify(stringifyDeepObjects(params.data)),
          method: 'POST',
        },
        url: collectionUrl,
      });
    case DELETE:
      return Promise.resolve({
        options: {
          method: 'DELETE',
        },
        url: itemUrl,
      });
    case GET_LIST:
    case GET_MANY_REFERENCE: {
      const {
        pagination: { page, perPage },
        sort: { field, order },
      } = params;

      if (order) collectionUrl.searchParams.set(`order[${field}]`, order);
      if (page) collectionUrl.searchParams.set('page', page);
      if (perPage) collectionUrl.searchParams.set('itemsPerPage', perPage);

      if (params.filter) {
        const buildFilterParams = (key, nestedFilter, rootKey) => {
          const filterValue = nestedFilter[key];

          if (Array.isArray(filterValue)) {
            filterValue.forEach((arrayFilterValue, index) => {
              collectionUrl.searchParams.set(`${rootKey}[${index}]`, arrayFilterValue);
            });
            return;
          }

          if (!isPlainObject(filterValue)) {
            collectionUrl.searchParams.set(rootKey, filterValue);
            return;
          }

          Object.keys(filterValue).forEach((subKey) => {
            if (
              rootKey === 'exists' ||
              [
                'after',
                'before',
                'strictly_after',
                'strictly_before',
                'lt',
                'gt',
                'lte',
                'gte',
                'between',
              ].includes(subKey)
            ) {
              return buildFilterParams(subKey, filterValue, `${rootKey}[${subKey}]`);
            }
            buildFilterParams(subKey, filterValue, `${rootKey}.${subKey}`);
          });
        };

        Object.keys(params.filter).forEach((key) => {
          buildFilterParams(key, params.filter, key);
        });
      }

      if (type === GET_MANY_REFERENCE && params.target) {
        collectionUrl.searchParams.set(params.target, params.id);
      }

      return Promise.resolve({
        options: {},
        url: collectionUrl,
      });
    }
    case GET_ONE:
      return Promise.resolve({
        options: {},
        url: itemUrl,
      });
    case UPDATE:
      return Promise.resolve({
        options: {
          body: JSON.stringify(stringifyDeepObjects(params.data)),
          method: 'PUT',
        },
        url: itemUrl,
      });
    default:
      throw new Error(`Unsupported fetch action type ${type}`);
  }
};

const createHydraResponseToReactAdminResponseConverter = (type) => (response) => {
  switch (type) {
    case GET_LIST:
    case GET_MANY_REFERENCE:
      return Promise.resolve(
        response.json['hydra:member'].map((doc) => jsonLdDocumentToReactAdminDocument(doc))
      ).then((data) => ({
        data,
        total:
          response.json?.['hydra:totalItems'] ||
          (response.json?.['hydra:view']
            ? response.json['hydra:view']?.['hydra:next']
              ? -2 // there is a next page
              : -1 // no next page
            : -3), // no information
      }));

    case DELETE:
      return Promise.resolve({ data: { id: null } });

    default:
      return Promise.resolve(jsonLdDocumentToReactAdminDocument(response.json)).then((data) => ({
        data,
      }));
  }
};

const normalizeObject = (obj) =>
  obj['@id']
    ? {
        ...obj,
        originId: obj.id,
        id: obj['@id'],
      }
    : obj;

export const jsonLdDocumentToReactAdminDocument = (document) => {
  let obj = normalizeObject(JSON.parse(JSON.stringify(document)));

  Object.keys(obj).forEach((key) => {
    // to-one
    if (isPlainObject(obj[key]) && obj[key]['@id']) {
      obj[key] = normalizeObject(obj[key]);
      cache[obj[key]['@id']] = jsonLdDocumentToReactAdminDocument(document[key]);

      return;
    }

    // to-many
    if (
      Array.isArray(obj[key]) &&
      obj[key].length &&
      isPlainObject(obj[key][0]) &&
      obj[key][0]['@id']
    ) {
      obj[key] = obj[key].map((object) => {
        cache[object['@id']] = jsonLdDocumentToReactAdminDocument(object);
        return normalizeObject(object);
      });
    }
  });

  return obj;
};

export default (entrypoint, httpClient) => {
  const reactAdminRequestConverter = createReactAdminToHydraRequestConverter(entrypoint);
  const fetchApi = (type, resource, params) =>
    reactAdminRequestConverter(type, resource, params)
      .then(({ url, options }) => httpClient(url, options))
      .then(createHydraResponseToReactAdminResponseConverter(type));

  return {
    getList: (resource, params) => fetchApi(GET_LIST, resource, params),
    getOne: (resource, params) => fetchApi(GET_ONE, resource, params),
    getMany: (resource, params) => {
      if (
        [
          /* put resources names that supports many here */
        ].includes(resource)
      ) {
        return fetchApi(GET_LIST, resource, {
          pagination: {},
          sort: {},
          filter: { id: params.ids },
        });
      }
      return Promise.all(
        params.ids.map((id) =>
          cache[id]
            ? Promise.resolve({ data: cache[id] })
            : fetchApi(GET_ONE, resource, {
                id,
              })
        )
      ).then((responses) => ({ data: responses.map(({ data }) => data) }));
    },
    getManyReference: (resource, params) => fetchApi(GET_MANY_REFERENCE, resource, params),
    update: (resource, params) => fetchApi(UPDATE, resource, params),
    updateMany: (resource, params) =>
      Promise.all(params.ids.map((id) => fetchApi(UPDATE, resource, { id }))).then(() => ({
        data: [],
      })),
    create: (resource, params) => fetchApi(CREATE, resource, params),
    delete: (resource, params) => fetchApi(DELETE, resource, params),
    deleteMany: (resource, params) =>
      Promise.all(params.ids.map((id) => fetchApi(DELETE, resource, { id }))).then(() => ({
        data: [],
      })),
  };
};
