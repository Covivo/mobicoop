import get from 'lodash.get';
import { useTranslate } from 'react-admin';

import { solidarySearchFrequencyLabels } from '../../../../constants/solidarySearchFrequency';

export const FrequencyField = ({ record, source }) => {
  const translate = useTranslate();
  const frequency = get(record, source);

  return translate(solidarySearchFrequencyLabels[frequency]) || '-';
};
