import React, { useState, useEffect, useCallback } from 'react';
import PropTypes from 'prop-types';
import { FormControlLabel, RadioGroup, Radio, Box } from '@material-ui/core';
import { useField } from 'react-final-form';
import { DateInput, required } from 'react-admin';
import { setTimeFromString } from './DateTimeSelector';

/*
choices = [
    { id:0, label:"Sur une période fixe" }
    { id:1, label:"La semaine prochaine", offsetDays: 7, offsetMonth: 0 },
    { id:2, label:"Le mois prochain", offsetDays: 0, offsetMonth: 1 }
]

type : DateTimeSelector.time, DateTimeSelector.date, DateTimeSelector.datetime
*/

export const getTime = (date) =>
  `${(date.getHours() < 10 ? '0' : '') + date.getHours()}:${
    (date.getMinutes() < 10 ? '0' : '') + date.getMinutes()
  }`;

const computeChoiceOffsets = (choice, date) => {
  if (choice.offsetDays || choice.offsetMonth) {
    date.setDate(date.getDate() + (choice.offsetDays || 0));
    date.setMonth(date.getMonth() + (choice.offsetMonth || 0));
  }

  return date;
};

const DateIntervalSelector = ({ fieldnameStart, fieldnameEnd, choices, initialChoice, type }) => {
  const {
    input: { value: valueStart },
  } = useField(fieldnameStart);

  const {
    input: { value: valueEnd, onChange: onChangeEnd },
  } = useField(fieldnameEnd);

  const [choice, setChoice] = useState(choices[initialChoice]);

  const handleChange = (value) => {
    const now = valueStart ? new Date(valueStart) : new Date();
    const endDate = new Date(
      now.getFullYear(),
      now.getMonth(),
      now.getDate(),
      now.getHours(),
      now.getMinutes()
    );

    if (value < choices.length) {
      onChangeEnd(computeChoiceOffsets(choices[value], endDate));
      setChoice(choices[value]);
    }
  };

  const handleStartFieldParse = useCallback(
    (dateString) => {
      const valueStartTime = getTime(
        typeof valueStart === 'string' ? new Date(valueStart) : valueStart
      );

      return setTimeFromString(new Date(dateString), valueStartTime || '00:00');
    },
    [valueStart]
  );

  useEffect(() => {
    // Keep outwardDatetime in sync with the outwardDeadline one
    if (valueStart && valueEnd) {
      const newEndDate = new Date(valueStart.toString());
      if (choice.offsetDays || choice.offsetMonth) {
        computeChoiceOffsets(choice, newEndDate);
      }

      onChangeEnd(newEndDate);
    }
  }, [valueStart && valueStart.toString()]);

  return (
    <Box display="flex" flexDirection="column">
      <DateInput
        label="A partir du "
        parse={handleStartFieldParse}
        source={fieldnameStart}
        validate={[required()]}
      />
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
