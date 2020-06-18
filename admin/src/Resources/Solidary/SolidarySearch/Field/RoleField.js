import get from 'lodash.get';
import { useTranslate } from 'react-admin';

import { carpoolRoleLabels } from '../../../../constants/solidarySearchRole';

export const RoleField = ({ record, source }) => {
  const translate = useTranslate();
  const role = get(record, source);

  return translate(carpoolRoleLabels[role]);
};
