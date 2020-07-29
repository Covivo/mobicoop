import React from 'react';
import PropTypes from 'prop-types';
import { useField } from 'react-final-form';
import Switch from '@material-ui/core/Switch';
import FormControlLabel from '@material-ui/core/FormControlLabel';

/*
Frequency : 
1 = punctual
2 = regular
*/

const SolidaryFrequency = ({ source, label, defaultValue }) => {
  const {
    input: { value, onChange },
  } = useField(source, { initialValue: defaultValue });

  return (
    <FormControlLabel
      control={
        // eslint-disable-next-line react/jsx-wrap-multilines
        <Switch
          checked={value === 2}
          onChange={(e) => (e.target.checked ? onChange(2) : onChange(1))}
        />
      }
      label={label || source}
    />
  );
};

SolidaryFrequency.propTypes = {
  source: PropTypes.string.isRequired,
  label: PropTypes.string,
  defaultValue: PropTypes.number,
};

SolidaryFrequency.defaultProps = {
  label: '',
  defaultValue: null,
};

export default SolidaryFrequency;
