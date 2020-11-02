import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import Chip from '@material-ui/core/Chip';
import { useField } from 'react-final-form';
import { makeStyles } from '@material-ui/core/styles';

const useStyles = makeStyles({
  spaceRight: {
    marginRight: '0.5rem',
  },
});

const DayChipInput = ({ label, source, onChange: onChangeInput, forcedValue, initialValue }) => {
  const {
    input: { value, onChange },
  } = useField(source);
  const classes = useStyles();
  const color = value ? 'primary' : 'default';
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (forcedValue !== undefined) {
      onChange(forcedValue);
    }
  }, [forcedValue]);

  useEffect(() => {
    if (loading) {
      console.log('[EDITION] Setting inital Valie:', initialValue);
      if (initialValue) onChange(initialValue);
      setLoading(false);
    }
  }, [initialValue]);

  return (
    <>
      {!loading
      && <Chip
        label={label}
        color={color}
        onClick={() => {
          onChangeInput(!value);
          return onChange(!value);
        }}
        className={classes.spaceRight}
      /> }
    </>
  );
};

DayChipInput.defaultProps = {
  onChange: () => {},
  forcedValue: undefined,
  initialValue: undefined,
};

DayChipInput.propTypes = {
  label: PropTypes.string.isRequired,
  source: PropTypes.string.isRequired,
  onChange: PropTypes.func,
  forcedValue: PropTypes.bool,
  initialValue: PropTypes.bool,
};

export default DayChipInput;
