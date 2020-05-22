import React, { useState } from 'react';
import { DateTimeInput } from 'react-admin';
import { FormControlLabel, RadioGroup, Radio, Box, TextField } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';

const useStyles = makeStyles({
  invisible: { display: 'none' },
  dateControlWitdh: { width: '200px' },
});

/*

choices = [
    { id:0, label="A une date fixe",  offsetHour=0, offsetDays=7, fromHour=0}
    { id:1, label="Dans la semaine", offsetHour=0, offsetDays=7},
    { id:2, label="Dans le mois", offsetHour=0, offsetDays=14}
]

type : DateTimeSelector.time, DateTimeSelector.date, DateTimeSelector.datetime

I missed Typescript
*/

const DateTimeSelector = ({
  form,
  fieldnameStart,
  fieldnameEnd,
  choices,
  initialChoice,
  type = 'date',
  initialStart,
}) => {
  const classes = useStyles();
  const [choice, setChoice] = useState(choices[initialChoice]);

  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const currentFromDateTime = () =>
    form && fieldnameStart && form.getState().values[fieldnameStart]
      ? new Date(form.getState().values[fieldnameStart])
      : today;
  const currentToDateTime = () =>
    form && fieldnameStart && form.getState().values[fieldnameEnd]
      ? new Date(form.getState().values[fieldnameEnd])
      : today;

  const setOffset = (hours, days, fromHour) => {
    // offset calculation of endDate, according to an offset applied to startDate
    var OffsetDate = new Date(today);
    OffsetDate.setDate(OffsetDate.getDate() + days || 0);
    OffsetDate.setHours(OffsetDate.getHours() + (hours || 0) + (fromHour || 0));
    updatePartialDateTime(OffsetDate, false, true);

    var startDate = new Date(today);
    startDate.setHours(startDate.getHours() + (fromHour || 0));
    updatePartialDateTime(startDate, true, false);
  };

  const handleChange = (value) => {
    const newChoice = choices[value];
    console.log('handleChange :', choices[value]);
    if (newChoice.id > 0) {
      // relative date
      setOffset(newChoice.offsetHour, newChoice.offsetDays, newChoice.fromHour);
    } else {
      if (newChoice.id === 0) {
        // From date = To date
        form && fieldnameEnd && form.change(fieldnameEnd, null);
      } else {
        // No date
        form && fieldnameStart && form.change(fieldnameStart, null);
        form && fieldnameEnd && form.change(fieldnameEnd, null);
      }
    }
    setChoice(newChoice);
  };

  const updatePartialDateTime = (value, updateStart = true, updateEnd = true) => {
    console.log('Updated value :', value);
    var valueDate = value;
    if (typeof value === 'string') {
      if (/\d{2}:\d{2}/.test(value)) {
        valueDate = new Date(
          now.getFullYear(),
          now.getMonth(),
          now.getDate(),
          parseInt(value.slice(0, 2)),
          parseInt(value.slice(3, 5))
        );
      } else {
        valueDate = new Date(value);
      }
    }

    switch (type) {
      case 'datetime-local':
        updateStart && form && fieldnameStart && form.change(fieldnameStart, valueDate);
        updateEnd && form && fieldnameStart && form.change(fieldnameEnd, valueDate);
        break;
      case 'date':
        // Keep the time, set the date
        // value is like "2020-05-22"
        const alreadySetTime = currentFromDateTime();
        const updatedDate = valueDate;
        console.log('Keep the time, set the date :', alreadySetTime);
        alreadySetTime.setFullYear(updatedDate.getFullYear());
        alreadySetTime.setMonth(updatedDate.getMonth());
        alreadySetTime.setDate(updatedDate.getDate());
        console.log('Updated :', alreadySetTime);
        updateStart && form && fieldnameStart && form.change(fieldnameStart, alreadySetTime);
        updateEnd && form && fieldnameStart && form.change(fieldnameEnd, alreadySetTime);
        break;
      case 'time':
        // Keep the date, set the time
        // value is like "08:00"
        const alreadySetDate = currentFromDateTime();
        const updatedTime = valueDate;
        console.log('Keep the date, set the time :', alreadySetDate);
        alreadySetDate.setHours(updatedTime.getHours());
        alreadySetDate.setMinutes(updatedTime.getMinutes());
        alreadySetDate.setSeconds(updatedTime.getSeconds());
        console.log('Updated :', alreadySetDate);
        updateStart && form && fieldnameStart && form.change(fieldnameStart, alreadySetDate);
        updateEnd && form && fieldnameStart && form.change(fieldnameEnd, alreadySetDate);
        break;
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

export default DateTimeSelector;
