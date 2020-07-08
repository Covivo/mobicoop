import React from 'react';
import PropTypes from 'prop-types';
import { Chip } from '@material-ui/core';

const DayChip = ({ condition, label }) =>
  condition ? <Chip label={label} color="primary" /> : <Chip label={label} />;

DayChip.propTypes = {
  condition: PropTypes.bool,
  label: PropTypes.string.isRequired,
};

DayChip.defaultProps = {
  condition: false,
};

export default DayChip;
