import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { FormControlLabel, RadioGroup, Radio, Box } from '@material-ui/core';
import { useField } from 'react-final-form';
import { DateInput, required } from 'react-admin';

/*

choices = [
    { id:0, label:"Sur une période fixe", }
    { id:1, label:"La semaine prochaine", offsetDays:7, offsetMonth:0},
    { id:2, label:"Le mois prochain", offsetDays:0, offsetMonth:1}
]

type : DateTimeSelector.time, DateTimeSelector.date, DateTimeSelector.datetime

*/

const DateIntervalSelector = ({ fieldnameStart, fieldnameEnd, choices, initialChoice, type }) => {
  const {
    input: { value: valueStart },
  } = useField(fieldnameStart);
  const {
    input: { onChange: onChangeEnd },
  } = useField(fieldnameEnd);

  const [choice, setChoice] = useState(choices[initialChoice]);

  const handleChange = (value) => {
    const now = valueStart ? new Date(valueStart) : new Date();
    const endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    if (value < choices.length) {
      if (choices[value].offsetDays || choices[value].offsetMonth) {
        endDate.setDate(endDate.getDate() + (choices[value].offsetDays || 0));
        endDate.setMonth(endDate.getMonth() + (choices[value].offsetMonth || 0));
        onChangeEnd(endDate);
      }
      setChoice(choices[value]);
    }
  };

  return (
    <Box display="flex" flexDirection="column">
      <DateInput label="A partir du " source={fieldnameStart} validate={[required()]} />

      <RadioGroup value={choice.id} onChange={(e) => handleChange(e.target.value)}>
        {choices.map((c) => (
          <FormControlLabel key={c.id} value={c.id} control={<Radio />} label={c.label} />
        ))}
      </RadioGroup>

      <DateInput label="Jusqu'au " source={fieldnameEnd} validate={[required()]} />
    </Box>
  );
};

DateIntervalSelector.propTypes = {
  fieldnameStart: PropTypes.string.isRequired,
  fieldnameEnd: PropTypes.string.isRequired,
  choices: PropTypes.array.isRequired,
  initialChoice: PropTypes.number,
  type: PropTypes.string,
};

DateIntervalSelector.defaultProps = {
  initialChoice: 0,
  type: 'date',
};

export default DateIntervalSelector;
