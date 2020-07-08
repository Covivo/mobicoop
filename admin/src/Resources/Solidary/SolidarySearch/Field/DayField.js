import React from 'react';
import get from 'lodash.get';

export const DayField = ({ record, source }) => {
  const morning = get(record, source).m;
  const afternoon = get(record, source).a;
  const evening = get(record, source).e;

  const display = [morning ? 'Mat.' : '-', afternoon ? 'Ap.' : '-', evening ? 'Soir' : '-'].join(
    '<br/>/'
  );

  // eslint-disable-next-line react/no-danger
  return <div dangerouslySetInnerHTML={{ __html: display }} />;
};
