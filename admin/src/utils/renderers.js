import React from 'react';

export const addressRenderer = (address) =>
  address && address.displayLabel && address.displayLabel.length
    ? `${address.displayLabel[0]} - ${address.displayLabel[1]}`
    : '';

export const journeyRenderer = ({ origin, destination }) => `${origin} -> ${destination}`;

export const usernameRenderer = ({ record }) => `${record.givenName} ${record.familyName}`;

export const UserRenderer = ({ record }) => <span>{usernameRenderer({ record })} </span>;

export const solidaryLabelRenderer = ({ record }) =>
  record.originId
    ? `#${record.originId} - ${record.displayLabel} ${
        record.solidaryUser
          ? `/ ${usernameRenderer({
              record: record.solidaryUser.user,
            })}`
          : ''
      }`
    : '';

export const solidaryJourneyRenderer = (solidary) =>
  solidary.origin &&
  solidary.destination &&
  solidary.origin.addressLocality &&
  solidary.destination.addressLocality
    ? journeyRenderer({
        origin: solidary.origin.addressLocality,
        destination: solidary.destination.addressLocality,
      })
    : null;
