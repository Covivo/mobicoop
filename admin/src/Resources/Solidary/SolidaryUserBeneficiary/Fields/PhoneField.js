import React from 'react';
import PropTypes from 'prop-types';

const formatPhone = (numb) => {
  const chuncks = numb.match(/.{1,2}/g);
  return chuncks.join(' ');
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
