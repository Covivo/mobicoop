import React from 'react';
import PropTypes from 'prop-types';
import Chip from '@material-ui/core/Chip';
import { useField } from 'react-final-form';
import { makeStyles } from '@material-ui/core/styles';

const useStyles = makeStyles({
  spaceRight: {
    marginRight: '0.5rem',
  },
});

const DayChipInput = ({ label, source }) => {
  const {
    input: { value, onChange },
  } = useField(source);
  const classes = useStyles();
  const color = value ? 'primary' : 'default';

  return (
    <Chip
      label={label}
      color={color}
      onClick={() => onChange(!value)}
      className={classes.spaceRight}
    />
  );
};

DayChipInput.propTypes = {
  label: PropTypes.string.isRequired,
  source: PropTypes.string.isRequired,
};

export default DayChipInput;
