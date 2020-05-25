import React from 'react';
import PropTypes from 'prop-types';

export const DayField = ({ record, source }) => {
  const morning = record[`m${source}`];
  const afternoon = record[`a${source}`];
  const evening = record[`e${source}`];

  const display = [morning ? 'Mat.' : '-', afternoon ? 'Ap.' : '-', evening ? 'Soir' : '-'].join(
    '<br/>/'
  );

  // eslint-disable-next-line react/no-danger
  return <div dangerouslySetInnerHTML={{ __html: display }} />;
};

DayField.propTypes = {
  record: PropTypes.object.isRequired,
  source: PropTypes.string.isRequired,
};
