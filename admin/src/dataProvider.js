import dataProvider from '@api-platform/admin/lib/hydra/dataProvider';
import { dataProviderAdapter } from './dataProviderAdapter';
import { fetchJson } from './fetchJson';

const getEmptyHydraSchema = () => Promise.resolve({ api: { resources: [] } });

const hydraDataProvider = dataProvider(
  process.env.REACT_APP_API,
  fetchJson,
  getEmptyHydraSchema,
  true
);

// "transformReactAdminDataToRequestBody" from original data provider returns a Promise.resolve
// It doesn't stringify data before sending it, so we take care by ourself to stringify it
// Original data provider => https://github.com/api-platform/admin/blob/master/src/hydra/dataProvider.js
const applyActionStringify = (originalFunc) => (resource, params) =>
  originalFunc(resource, { ...params, data: JSON.stringify(params.data) });

hydraDataProvider.update = applyActionStringify(hydraDataProvider.update);
hydraDataProvider.create = applyActionStringify(hydraDataProvider.create);

// Override getMany because of "hasIdSearchFilter" that need to have a schema entry for each resource
// https://github.com/api-platform/admin/blob/master/src/hydra/dataProvider.js#L418
hydraDataProvider.getMany = (resource, params) =>
  Promise.all(
    params.ids.map((id) => hydraDataProvider.getOne(resource, { id }))
  ).then((responses) => ({ data: responses.map(({ data }) => data) }));

// Mimic HydraAdmin initialisation
// So that, the internal schema is initialised with "getEmptyHydraSchema" data
hydraDataProvider.introspect();

export default dataProviderAdapter(hydraDataProvider);
