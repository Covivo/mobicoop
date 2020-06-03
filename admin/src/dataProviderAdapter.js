import pick from 'lodash.pick';
import { fetchJson } from './fetchJson';

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
    'mMinTime',
    'mMaxTime',
    'aMinTime',
    'aMaxTime',
    'eMinTime',
    'eMaxTime',
  ]),
});

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

/**
 * Custom getOne Provider for "users"
 * Because we need to map roles territies
 */
const getOneUser = async (provider, params) => {
  const { data: user } = await provider.getOne('users', {
    id: params.id.search('users') === -1 ? `users/${params.id}` : params.id,
  });

  const rolesTerritory = await Promise.all(
    user.userAuthAssignments.map((element) =>
      provider
        .getOne('userAuthAssignments', { id: element })
        .then(({ data }) => data)
        .catch((error) => {
          console.log('An error occured during user rights retrieving:', error);
        })
    )
  );

  user.rolesTerritory = rolesTerritory.filter((element) => userRoles.includes(element.authItem));

  return { data: user };
};

const extractRoles = (fields) => {
  const newRoles = [];

  fields.forEach((v) => {
    const territory = v.territory;
    // There is many roles
    if (Array.isArray(v.roles)) {
      v.roles.forEach((r) => {
        v != null ? newRoles.push({ authItem: r, territory }) : newRoles.push({ authItem: r });
      });
      // There is just 1 roles
    } else {
      v != null
        ? newRoles.push({ authItem: v.roles, territory })
        : newRoles.push({ authItem: v.roles });
    }
  });

  return newRoles;
};

/**
 * Apply a custom logic on user roles before update
 */
const updateUser = async (provider, params) => {
  const newParams = { ...params };

  newParams.data.userAuthAssignments =
    newParams.data.fields != null
      ? extractRoles(newParams.data.fields)
      : newParams.data.rolesTerritory.map(({ territory, authItem }) =>
          territory != null ? { authItem, territory } : { authItem }
        );

  return provider.update('users', {
    id: newParams.id,
    data: newParams.data,
    previousData: newParams.data.previousData,
  });
};

/**
 * Apply custom logic on user create
 */
const createUser = async (provider, params) => {
  const newParams = { ...params };

  newParams.data.userAuthAssignments = extractRoles(newParams.data.fields);
  newParams.data.addresses = [
    {
      ...newParams.data.address,
      home: true,
    },
  ];

  /* Add custom fields fo fit with api */
  newParams.data.passwordSendType = 1;
  newParams.data.language = 'fr_FR';
  newParams.data.userDelegate = `/users/${global.localStorage.getItem('id')}`;
  /* Add custom fields fo fit with api */

  return fetchJson(process.env.REACT_APP_API + process.env.REACT_APP_CREATE_USER, {
    method: 'POST',
    body: JSON.stringify(newParams.data),
  }).then(({ json }) => ({
    data: { ...newParams.data, id: json.id },
  }));
};

export const dataProviderAdapter = (originalProvider) => ({
  ...originalProvider,
  getOne: (resource, params) => {
    if (resource === 'users') {
      return getOneUser(originalProvider, params);
    }

    return originalProvider.getOne(resource, params);
  },
  getMany: (resource, params) => {
    if (resource === 'addresses') {
      // Adapt resource access for addresses
      return originalProvider.getMany('addresses/search', params);
    }

    return originalProvider.getMany(resource, params);
  },
  getList: (resource, params) => {
    if (resource === 'communities') {
      // Adapt resource access for communities
      return originalProvider.getList('communities/manage', params);
    }

    return originalProvider.getList(resource, params);
  },
  create: (resource, params) => {
    if (resource === 'users') {
      return createUser(originalProvider, params);
    }

    return originalProvider.create(resource, params);
  },
  update: (resource, params) => {
    let newParams = transformId({ ...params });
    if (resource === 'users') {
      return updateUser(originalProvider, newParams);
    }

    if (resource === 'solidary_volunteers') {
      newParams = pickManagedSolidaryVolunteerData(newParams);
    }

    return originalProvider.update(resource, newParams);
  },
});
