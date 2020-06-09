import React from 'react';
import PropTypes from 'prop-types';
import CheckIcon from '@material-ui/icons/Check';
import ClearIcon from '@material-ui/icons/Clear';
import NotListedLocationIcon from '@material-ui/icons/NotListedLocation';

export const YesNoField = ({ record, source }) => {
  if (![true, false].includes(record[source])) {
    return <NotListedLocationIcon />;
  }

  return (
    <span style={{ color: record[source] ? 'green' : 'red' }}>
      {record[source] ? <CheckIcon /> : <ClearIcon />}
    </span>
  );
};

YesNoField.propTypes = {
  record: PropTypes.object.isRequired,
  source: PropTypes.string.isRequired,
};
