import get from 'lodash.get';

export const JourneyField = ({ record, source }) => {
  const journey = get(record, source);
  return `${journey.origin} -> ${journey.destination}`;
};
