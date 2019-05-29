import { stringify } from 'query-string';
import {
    fetchUtils,
    GET_LIST,
    GET_ONE,
    GET_MANY,
    GET_MANY_REFERENCE,
    CREATE,
    UPDATE,
    DELETE,
} from 'react-admin';
import isPlainObject from 'lodash.isplainobject';

class ReactAdminDocument {
    constructor(obj) {
      Object.assign(this, obj, {
        id: obj.id,
        hydraId: obj['@id'],
      });
    }
  
    /**
     * @return {string}
     */
    toString() {
      return `[object ${this.id}]`;
    }
  }
  
  /**
   * Local cache containing embedded documents.
   * It will be used to prevent useless extra HTTP query if the relation is displayed.
   *
   * @type {Map}
   */
  const reactAdminDocumentsCache = new Map();
  
  /**
   * Transforms a JSON-LD document to a react-admin compatible document.
   *
   * @param {Object} document
   * @param {bool} clone
   *
   * @return {ReactAdminDocument}
   */
  export const transformJsonLdDocumentToReactAdminDocument = (
    document,
    clone = true,
    addToCache = true,
  ) => {
    if (clone) {
      // deep clone documents
      document = JSON.parse(JSON.stringify(document));
    }
  
    // The main document is a JSON-LD document, convert it and store it in the cache
    if (document['@id']) {
      document = new ReactAdminDocument(document);
    }
  
    // Replace embedded objects by their IRIs, and store the object itself in the cache to reuse without issuing new HTTP requests.
    Object.keys(document).forEach(key => {
      // to-one
      if (isPlainObject(document[key]) && document[key]['@id']) {
        if (addToCache) {
          reactAdminDocumentsCache[
            document[key]['@id']
          ] = transformJsonLdDocumentToReactAdminDocument(
            document[key],
            false,
            false,
          );
        }
        document[key] = document[key]['@id'];
  
        return;
      }
  
      // to-many
      if (
        Array.isArray(document[key]) &&
        document[key].length &&
        isPlainObject(document[key][0]) &&
        document[key][0]['@id']
      ) {
        document[key] = document[key].map(obj => {
          if (addToCache) {
            reactAdminDocumentsCache[
              obj['@id']
            ] = transformJsonLdDocumentToReactAdminDocument(obj, false, false);
          }
  
          return obj['@id'];
        });
      }
    });
  
    return document;
  };


/**
 * Maps react-admin queries to a Mobicoop API
 *
 * @example
 * GET_LIST     => GET http://my.api.url/posts
 * GET_ONE      => GET http://my.api.url/posts/123
 * GET_MANY     => GET http://my.api.url/posts/123, GET http://my.api.url/posts/456, GET http://my.api.url/posts/789
 * UPDATE       => PUT http://my.api.url/posts/123
 * CREATE       => POST http://my.api.url/posts
 * DELETE       => DELETE http://my.api.url/posts/123
 */
export default (apiUrl, httpClient = fetchUtils.fetchJson) => {
    /**
     * @param {String} type One of the constants appearing at the top if this file, e.g. 'UPDATE'
     * @param {String} resource Name of the resource to fetch, e.g. 'posts'
     * @param {Object} params The data request params, depending on the type
     * @returns {Object} { url, options } The HTTP request parameters
     */
    const convertDataRequestToHTTP = (type, resource, params) => {
        let url = '';
        const options = {};
        const entrypointUrl = new URL(apiUrl, window.location.href);
        const collectionUrl = new URL(`${apiUrl}/${resource}`, entrypointUrl);
        //const itemUrl = new URL(params.id, entrypointUrl);

        switch (type) {
            case GET_LIST: {
                const {
                    pagination: {page, perPage},
                    sort: {field, order},
                } = params;
        
                if (order) collectionUrl.searchParams.set(`order[${field}]`, order);
                if (page) collectionUrl.searchParams.set('page', page);
                if (perPage) collectionUrl.searchParams.set('perPage', perPage);
                if (params.filter) {
                    Object.keys(params.filter).forEach(key => {
                        const filterValue = params.filter[key];
                        if (!isPlainObject(filterValue)) {
                            collectionUrl.searchParams.set(key, params.filter[key]);
                            return;
                        }
            
                        Object.keys(filterValue).forEach(subKey => {
                            collectionUrl.searchParams.set(
                                `${key}[${subKey}]`,
                                filterValue[subKey],
                            );
                        });
                    });
                }

                url = collectionUrl;
                break;
            }
            case GET_ONE:
                url = `${apiUrl}/${resource}/${params.id}`;
                break;
            case GET_MANY_REFERENCE: {
                console.log('ici');
                if (params.target) {
                    collectionUrl.searchParams.set(params.target, params.id);
                }
                return Promise.resolve({
                    options: {},
                    url: collectionUrl,
                });
            }
            case UPDATE:
                url = `${apiUrl}/${resource}/${params.id}`;
                options.method = 'PUT';
                options.body = JSON.stringify(params.data);
                break;
            case CREATE:
                url = `${apiUrl}/${resource}`;
                options.method = 'POST';
                options.body = JSON.stringify(params.data);
                break;
            case DELETE:
                url = `${apiUrl}/${resource}/${params.id}`;
                options.method = 'DELETE';
                break;
            default:
                throw new Error(`Unsupported fetch action type ${type}`);
        }
        return { url, options };
    };

    /**
     * @param {Object} response HTTP response from fetch()
     * @param {String} type One of the constants appearing at the top if this file, e.g. 'UPDATE'
     * @param {String} resource Name of the resource to fetch, e.g. 'posts'
     * @param {Object} params The data request params, depending on the type
     * @returns {Object} Data response
     */
    const convertHTTPResponse = (response, type, resource, params) => {
        const { headers, json } = response;
        switch (type) {
            case GET_LIST:
            case GET_MANY_REFERENCE:
                return {
                    data: response.json['hydra:member'].map(
                        transformJsonLdDocumentToReactAdminDocument,
                    ),
                    total: json['hydra:totalItems']
                };
            case CREATE:
                return { data: { ...params.data, id: json.id } };
            default:
                return { data: transformJsonLdDocumentToReactAdminDocument(response.json) };
        }
    };

    /**
     * @param {string} type Request type, e.g GET_LIST
     * @param {string} resource Resource name, e.g. "posts"
     * @param {Object} payload Request parameters. Depends on the request type
     * @returns {Promise} the Promise for a data response
     */
    return (type, resource, params) => {
        if (type === GET_MANY) {
            return Promise.all(
                params.ids.map(obj => {
                    if (obj.id) {
                        return httpClient(`${apiUrl}/${resource}/${obj.id}`, {
                            method: 'GET'
                        });
                    }
                    return httpClient(`${apiUrl}/${resource}/${obj}`, {
                        method: 'GET'
                    });
                })
            ).then(responses => ({
                data: responses.map(response => response.json),
            }));
        }

        const { url, options } = convertDataRequestToHTTP(
            type,
            resource,
            params
        );
        return httpClient(url, options).then(response =>
            convertHTTPResponse(response, type, resource, params)
        );
    };
};