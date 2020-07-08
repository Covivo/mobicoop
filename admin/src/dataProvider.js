import { GET_LIST } from 'react-admin';

import { dataProviderAdapter } from './dataProviderAdapter';
import { fetchJson } from './fetchJson';
import hydraDataProvider from './hydraDataProvider';

/**
 * This transformer allows to transform API response before hydra to react transform
 * This is usefull for "non-standard" (no jsonld) endpoints such as solidary_searches
 */
const customResponseTransformer = (type, resource) => (response) => {
  if (type === GET_LIST && resource === 'solidary_searches') {
    response.json['hydra:member'] = response.json.results.map((item, index) => ({
      ...item,
      id: index, // The id is always 999999999 from the api, so we transform it for react-admin
    }));
  }

  return response;
};

const customErrorHandler = (type, resource) => (error) => {
  if (type === GET_LIST && resource === 'solidary_searches') {
    // We have an error if there's no results for the moment...
    // So return an empty array in this case
    // @TODO: Fix the api for "Call to a member function setOrigin() on null"
    if (error.status === 500) {
      return { json: { ['hydra:member']: [] } };
    }
  }

  throw error;
};

export default dataProviderAdapter(
  hydraDataProvider(
    process.env.REACT_APP_API,
    fetchJson,
    customResponseTransformer,
    customErrorHandler,
    true
  )
);
