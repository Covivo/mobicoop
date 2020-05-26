import React from 'react';
import PropTypes from 'prop-types';

export const RoleField = ({ record, source, fillRoleLabel, unfulfillRoleLabel }) => {
  if (typeof record[source] === 'undefined') {
    return 'N/A';
  }

  return <span>{record[source] ? fillRoleLabel : unfulfillRoleLabel}</span>;
};

RoleField.propTypes = {
  record: PropTypes.object.isRequired,
  source: PropTypes.string.isRequired,
  fillRoleLabel: PropTypes.string.isRequired,
  unfulfillRoleLabel: PropTypes.string.isRequired,
};
