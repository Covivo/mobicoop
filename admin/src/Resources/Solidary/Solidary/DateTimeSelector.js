import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { FormControlLabel, RadioGroup, Radio, Box, TextField } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { useField } from 'react-final-form';

const useStyles = makeStyles({
  invisible: { display: 'none' },
  dateControlWitdh: { width: '200px' },
});

/*

choices = [
    { id:0, label="A une date fixe",  offsetHour:0, offsetDays:7, fromHour:0}
    { id:1, label="Dans la semaine", offsetHour:0, offsetDays:7},
    { id:2, label="Dans le mois", offsetHour:0, offsetDays:14}
]

type : DateTimeSelector.time, DateTimeSelector.date, DateTimeSelector.datetime

I missed Typescript
*/

const DateTimeSelector = ({
  fieldnameStart,
  fieldnameEnd,
  choices,
  initialChoice,
  type = 'date',
  initialStart,
  initialEnd,
}) => {
  const classes = useStyles();
  const [choice, setChoice] = useState(choices[initialChoice]);

  const {
    input: { value: valueStart, onChange: onChangeStart },
  } = useField(fieldnameStart);
  const {
    input: { value: valueEnd, onChange: onChangeEnd },
  } = useField(fieldnameEnd);
  const {
    input: { onChange: onChangeMargin },
  } = useField('margin');
  /*
  console.log(fieldnameStart, valueStart);
  console.log(fieldnameEnd, valueEnd);
  console.log('initialStart', initialStart);
  console.log('initialEnd', initialEnd);
*/
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  const updateEndDateByOffset = (originDate, hours, days) => {
    // offset calculation of endDate, according to an offset applied to originDate
    console.log('updateEndDateByOffset:', originDate, hours, days);
    const OffsetDate = new Date(originDate);
    OffsetDate.setDate(OffsetDate.getDate() + days || 0);
    OffsetDate.setHours(OffsetDate.getHours() + (hours || 0));
    updatePartialDateTime(OffsetDate, false, true);
  };

  const updateStartDateByOffset = (originDate, hours) => {
    // offset calculation of start date, according to an hourly offset fromHour applied to originDate
    const startDate = new Date(originDate);
    startDate.setHours(startDate.getHours() + (hours || 0));
    updatePartialDateTime(startDate, true, false);
  };

  const handleChange = (value) => {
    // Set datetime fields according to option value
    const newChoice = choices[value];
    if (newChoice.id > 0) {
      // relative date
      updateEndDateByOffset(initialEnd || today, newChoice.offsetHour, newChoice.offsetDays);
      updateStartDateByOffset(initialStart || today, newChoice.offsetHour);
      newChoice.margin && onChangeMargin(newChoice.margin);
    } else if (newChoice.id === 0 && type !== 'time') {
      // Clear toDate
      onChangeEnd(null);
    }
    setChoice(newChoice);
  };

  const buildDateObject = (source, initialDate = null) => {
    // Build a new Date object.
    // source can be :
    // - a Date object
    // - a string that can be regularly parsed by new Date(). Ex : "2020-06-03"
    // - a time string, like : 15:03
    if (typeof source === 'string' && /^\d{2}:\d{2}$/.test(source)) {
      // time only : use initialDate if any
      if (initialDate) {
        const formatedDateTime = `${initialDate.getFullYear()}-${initialDate.getMonth()}-${initialDate.getDate()} ${source}`;
        return new Date(formatedDateTime);
      }
      return new Date(`2020-01-02 ${source}`);
    }

    return new Date(source);
  };

  const updateDateOnly = (originTime, destinationDate) => {
    // Return a new date, which is destinationDate's year/month/day AND originDate's hour/minute/seconds
    const updatedDateTime = buildDateObject(originTime);
    const validDestinationDate = buildDateObject(destinationDate); // prevents from a null destinationDate
    updatedDateTime.setFullYear(validDestinationDate.getFullYear());
    updatedDateTime.setMonth(validDestinationDate.getMonth());
    updatedDateTime.setDate(validDestinationDate.getDate());
    return updatedDateTime;
  };

  const updatePartialDateTime = (value, updateStart = true, updateEnd = true) => {
    const updatedDateTime = buildDateObject(value);
    /*
    console.log('----updatePartialDateTime ', updateStart, updateEnd);
    console.log('typeof value:', typeof value);
    console.log('value:', value);
    console.log('type:', type);
    console.log('updatedDateTime:', updatedDateTime);
    */
    switch (type) {
      case 'datetime-local':
        // Update date and time
        updateStart && onChangeStart(updatedDateTime);
        updateEnd && onChangeEnd(updatedDateTime);
        break;
      case 'date':
        // Keep the time, set the date
        updateStart && onChangeStart(updateDateOnly(valueStart, updatedDateTime));
        updateEnd && onChangeEnd(updateDateOnly(valueEnd, updatedDateTime));
        break;
      case 'time':
        // Keep the date, set the time
        updateStart && onChangeStart(updateDateOnly(updatedDateTime, initialStart || valueStart));
        updateEnd && onChangeEnd(updateDateOnly(updatedDateTime, initialEnd || valueEnd));
        break;
      default:
        throw new Error('unknown type');
    }
  };

  return (
    <Box display="flex">
      <RadioGroup value={choice.id} onChange={(e) => handleChange(e.target.value)}>
        {choices.map((c) => (
          <FormControlLabel key={c.id} value={c.id} control={<Radio />} label={c.label} />
        ))}
      </RadioGroup>
      <Box>
        <div className={choice.id && classes.invisible}>
          <TextField
            label=""
            type={type}
            InputLabelProps={{
              shrink: true,
            }}
            onChange={(e) => updatePartialDateTime(e.target.value)}
            className={classes.dateControlWitdh}
          />
        </div>
      </Box>
    </Box>
  );
};

DateTimeSelector.type = { time: 'time', datetime: 'datetime-local', date: 'date' };

DateTimeSelector.propTypes = {
  fieldnameStart: PropTypes.string.isRequired,
  fieldnameEnd: PropTypes.string.isRequired,
  choices: PropTypes.array.isRequired,
  initialChoice: PropTypes.number,
  type: PropTypes.string,
  initialStart: PropTypes.object,
  initialEnd: PropTypes.object,
};

DateTimeSelector.defaultProps = {
  initialChoice: 0,
  type: 'date',
  initialStart: null,
  initialEnd: null,
};

export default DateTimeSelector;
