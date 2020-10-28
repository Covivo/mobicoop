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

const formatDate = (date) => {
  try {
    return (new Date(date)).toISOString().split('T')[0]
  } catch (e) {
    return date;
  }
};

const DateIntervalSelector = ({ fieldnameStart, fieldnameEnd, choices, initialChoice, type }) => {
  const {
    input: { value: valueStart, onChange: onChangeStart },
    meta: { initial: initialStart }
  } = useField(fieldnameStart);

  const {
    input: { value: valueEnd, onChange: onChangeEnd },
    meta: { initial: initialEnd }
  } = useField(fieldnameEnd);

  const [choice, setChoice] = useState(choices[initialChoice]);
  const [defaultStart, setDefaultStart] = useState(formatDate(initialStart || valueStart));
  const [defaultEnd, setDefaultEnd] = useState(formatDate(initialEnd || valueEnd));

  console.log('[EDITION][INTERVAL] start init: ', initialStart);
  console.log('[EDITION][INTERVAL] End init: ', initialEnd);

  useEffect(() => {
    onChangeStart(initialStart);
    onChangeEnd('2020-11-10');
  }, [initialEnd, initialStart]);

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
      setDefaultEnd(formatDate(computeChoiceOffsets(choices[value], endDate)));
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
      const newEndDate = new Date(initialEnd || valueStart.toString());
      if (choice.offsetDays || choice.offsetMonth) {
        computeChoiceOffsets(choice, newEndDate);
      }

      onChangeEnd(newEndDate);
      console.log('[EDITION][INTERVALE] New End Date', newEndDate);
      setDefaultEnd(formatDate(newEndDate));
    }
  }, [valueStart && valueStart.toString()]);

  return (
    <Box display="flex" flexDirection="column">
      <DateInput
        label="A partir du "
        parse={handleStartFieldParse}
        source={fieldnameStart}
        validate={[required()]}
        value={defaultStart}
        onChange={(e) => {
          setDefaultStart(e.target.value);
        }}
      />
      <RadioGroup value={choice.id} onChange={(e) => handleChange(e.target.value)}>
        {choices.map((c) => (
          <FormControlLabel key={c.id} value={c.id} control={<Radio />} label={c.label} />
        ))}
      </RadioGroup>
      <DateInput value={defaultEnd} label="Jusqu'au " source={fieldnameEnd} validate={[required()]}
                 onChange={(e) => {
                   setDefaultEnd(e.target.value);
                 }}
      />
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
