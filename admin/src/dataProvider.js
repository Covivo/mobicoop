import { GET_LIST, HttpError } from 'react-admin';

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

  if (type === GET_LIST && resource === 'solidary_animations') {
    response.json['hydra:member'] = response.json['hydra:member'].map((item, index) => ({
      ...item,
      id: index, // The id is always 999999999 from the api, so we transform it for react-admin
      '@id': item['@id'].replace('999999999999', index),
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

  // Handle custom error message when error occured because of missing associated structure for current user
  if (
    type === GET_LIST &&
    error.status === 500 &&
    error.body &&
    error.body['hydra:description'] === 'No structure found'
  ) {
    throw new HttpError(
      "Aucune demande solidaire affichée car votre compte n'est associé à aucune structure accompagnante en tant qu'opérateur"
    );
  }

  if (error.body && error.body['hydra:description']) {
    throw new HttpError(error.body['hydra:description']);
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
