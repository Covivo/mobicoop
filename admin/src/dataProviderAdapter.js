import pick from 'lodash.pick';

/**
 * This file aims to fix some API weaknesses
 * It therefore acts on a temporary basis until the API is fully able to handle the requests of the admin
 */

/**
 * The "id" field contains a string of this type "/api/voluntary/1" because of hydra mapper
 * The backend isn't able to handle string as id, so we transform it back to an "int" using originId
 */
const transformId = (params) => ({
  ...params,
  data: {
    ...params.data,
    id: params.data.originId,
  },
});

/**
 * The backend is not able to handle all the fields on PUT
 * For exemple, if homeAddress is null, it'll fail (but the original GET request returns null...)
 * So we only send the "managed" fields as data
 */
const pickManagedSolidaryVolunteerData = (params) => ({
  ...params,
  data: pick(params.data, [
    'validatedCandidate',
    'mMon',
    'mTue',
    'mWed',
    'mThu',
    'mFri',
    'mSat',
    'mSun',
    'aMon',
    'aTue',
    'aWed',
    'aThu',
    'aFri',
    'aSat',
    'aSun',
    'eMon',
    'eTue',
    'eWed',
    'eThu',
    'eFri',
    'eSat',
    'eSun',
  ]),
});

export const dataProviderAdapter = (originalProvider) => ({
  ...originalProvider,
  update: (resource, params) => {
    let newParams = transformId({ ...params });

    if (resource === 'solidary_volunteers') {
      newParams = pickManagedSolidaryVolunteerData(newParams);
    }

    return originalProvider.update(resource, newParams);
  },
});
