import React from 'react';
import PropTypes from 'prop-types';

export const formatPhone = (numb) => {
  const chuncks = numb.match(/.{1,2}/g);
  return <span dangerouslySetInnerHTML={{ __html: chuncks.join('&nbsp;') }} />;
};

export const PhoneField = ({ record, source }) => {
  if (!record[source]) {
    return '-';
  }

  return <span>{formatPhone(record[source])}</span>;
};

PhoneField.propTypes = {
  record: PropTypes.object.isRequired,
  source: PropTypes.string.isRequired,
};
