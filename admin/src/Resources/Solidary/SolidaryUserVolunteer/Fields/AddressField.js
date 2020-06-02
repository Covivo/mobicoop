import React from 'react';
import PropTypes from 'prop-types';

export const AddressField = ({ record, source }) => {
  if (!record[source]) {
    return '-';
  }

  return <span>{record[source].addressLocality}</span>;
};

AddressField.propTypes = {
  record: PropTypes.object.isRequired,
  source: PropTypes.string.isRequired,
};
