import get from 'lodash.get';
import { journeyRenderer } from '../../../../utils/renderers';

export const JourneyField = ({ record, source }) => {
  const journey = get(record, source);
  return journeyRenderer({ origin: journey.origin, destination: journey.destination });
};
