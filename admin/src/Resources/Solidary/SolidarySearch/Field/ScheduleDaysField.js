import React from 'react';
import get from 'lodash.get';

import { useTranslate } from 'react-admin';

export const ScheduleDaysField = ({ record, source }) => {
  const translate = useTranslate();
  const schedule = get(record, source);

  return (
    <span>
      {Object.keys(schedule)
        .map((day) => translate(`custom.days.${day}`))
        .join(' ')}
    </span>
  );
};
