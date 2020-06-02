import { fetchUtils } from 'react-admin';

export const fetchJson = (url, options = {}) =>
  fetchUtils
    .fetchJson(url, {
      ...options,
      headers: new global.Headers({
        Accept: 'application/ld+json', // Ask for JSONLD as response format
        Authorization: `Bearer ${global.localStorage.getItem('token')}`,
      }),
    })
    .then((response) => {
      if (response.status === 401) {
        global.window.location.href = '/#/login';
      }

      return response;
    });
