import { dataProviderAdapter } from './dataProviderAdapter';
import { fetchJson } from './fetchJson';
import hydraDataProvider from './hydraDataProvider';

export default dataProviderAdapter(hydraDataProvider(process.env.REACT_APP_API, fetchJson));
