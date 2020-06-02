import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import { FormControlLabel, RadioGroup, Radio, Box, TextField } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { useField } from 'react-final-form';

const useStyles = makeStyles({
  invisible: { display: 'none' },
  dateControlWitdh: { width: '200px' },
});

/*

const fromDateChoices = [
  { id: 0, label: 'A une date fixe', outwardDateTime : ({selectedDateTime}) => selectedDateTime },
  { id: 1, label: 'Dans la semaine', outwardDateTime : () => today(), outwardDeadlineDateTime : () => addDays(today, 7) },
  { id: 2, label: 'Dans la quinzaine', outwardDateTime : () => today(), outwardDeadlineDateTime : () => addDays(today,14) },
  { id: 3, label: 'Dans le mois', outwardDateTime : () => today(), outwardDeadlineDateTime : () => addDays(today,30) },
];

const fromTimeChoices = [
  { id: 0, label: 'A une heure fixe', outwardDateTime : ({selectedDateTime}) => selectedDateTime },
  { id: 1, label: 'Entre 8h et 13h', outwardDateTime : ({outwardDateTime}) => setHours(outwardDateTime, 8),  marginDuration: () => 5 * 3600 },
  { id: 2, label: 'Entre 13h et 18h', outwardDateTime : ({outwardDateTime}) => setHours(outwardDateTime, 13), marginDuration: () => 5 * 3600 },
  { id: 3, label: 'Entre 18h et 21h', outwardDateTime : ({outwardDateTime}) => setHours(outwardDateTime, 18), marginDuration: () => 3 * 3600 },
];

const toTimeChoices = [
  { id: 0, label: 'A une heure fixe',  returnDateTime : ({selectedDateTime}) => selectedDateTime },
  { id: 1, label: 'Une heure plus tard', returnDateTime : ({outwardDateTime}) => addHours(outwardDateTime, 1) },
  { id: 2, label: 'Deux heures plus tard',  returnDateTime : ({outwardDateTime}) => addHours(outwardDateTime,2)  },
  { id: 3, label: 'Trois heures plus tard',  returnDateTime : ({outwardDateTime}) => addHours(outwardDateTime,3)  },
  { id: 4, label: "Pas besoin qu'on me ramène", },
];

type : DateTimeSelector.time, DateTimeSelector.date, DateTimeSelector.datetime

I missed Typescript
*/

const now = new Date();
const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
const addDays = (originDate, days) => {
  const alteredDate = new Date(originDate);
  alteredDate.setDate(alteredDate.getDate() + days || 0);
  return alteredDate;
};

const addHours = (originDate, hours) => {
  const alteredDate = new Date(originDate);
  alteredDate.setHours(alteredDate.getHours() + hours || 0);
  return alteredDate;
};

const setHours = (originDate, hours) => {
  const alteredDate = new Date(originDate);
  alteredDate.setHours(hours || 0);
  alteredDate.setMinutes(0);
  return alteredDate;
};

// stringTime : "08:22"
const setTimeFromString = (originDate, stringTime) => {
  const alteredDate = originDate ? new Date(originDate) : today;
  const extractedHoursMinutes = stringTime && stringTime.match(/(\d{2})/g);
  if (extractedHoursMinutes && extractedHoursMinutes.length === 2) {
    alteredDate.setHours(parseInt(extractedHoursMinutes[0], 10));
    alteredDate.setMinutes(parseInt(extractedHoursMinutes[1], 10));
  }
  return alteredDate;
};

const setDateFromString = (originDate, stringDate) => {
  const alteredDate = originDate ? new Date(originDate) : today;
  const extractedYearMonthDay = stringDate && new Date(stringDate);
  if (extractedYearMonthDay) {
    alteredDate.setDate(extractedYearMonthDay.getDate());
    alteredDate.setMonth(extractedYearMonthDay.getMonth());
    alteredDate.setFullYear(extractedYearMonthDay.getFullYear());
  }
  return alteredDate;
};
const DateTimeSelector = ({ choices, initialChoice, type = 'date', depedencies }) => {
  const classes = useStyles();
  const [choice, setChoice] = useState(choices[initialChoice]);
  const [selectedDateTime, setSelectedDateTime] = useState(null);

  const {
    input: { value: outwardDateTime, onChange: onChangeOutwardDateTime },
  } = useField('outwardDateTime');
  const {
    input: { value: outwardDeadlineDateTime, onChange: onChangeOutwardDeadlineDateTime },
  } = useField('outwardDeadlineDateTime');
  const {
    input: { value: returnDateTime, onChange: onChangeReturnDateTime },
  } = useField('returnDateTime');
  const {
    input: { value: marginDuration, onChange: onChangeMarginDuration },
  } = useField('marginDuration');

  useEffect(() => {
    // Set datetime fields according to choice and selectedDateTime

    if (choice.outwardDateTime) {
      onChangeOutwardDateTime(
        choice.outwardDateTime({
          outwardDateTime,
          outwardDeadlineDateTime,
          returnDateTime,
          marginDuration,
          selectedDateTime,
        })
      );
    }
    if (choice.outwardDeadlineDateTime) {
      onChangeOutwardDeadlineDateTime(
        choice.outwardDeadlineDateTime({
          outwardDateTime,
          outwardDeadlineDateTime,
          returnDateTime,
          marginDuration,
          selectedDateTime,
        })
      );
    }
    if (choice.returnDateTime) {
      onChangeReturnDateTime(
        choice.returnDateTime({
          outwardDateTime,
          outwardDeadlineDateTime,
          returnDateTime,
          marginDuration,
          selectedDateTime,
        })
      );
    }
    if (choice.marginDuration) {
      onChangeMarginDuration(
        choice.marginDuration({
          outwardDateTime,
          outwardDeadlineDateTime,
          returnDateTime,
          marginDuration,
          selectedDateTime,
        })
      );
    }
  }, [choice, selectedDateTime, ...depedencies]);

  return (
    <Box display="flex">
      <RadioGroup value={choice.id} onChange={(e) => setChoice(choices[e.target.value])}>
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
            onChange={(e) => setSelectedDateTime(e.target.value)}
            className={classes.dateControlWitdh}
          />
        </div>
      </Box>
    </Box>
  );
};

DateTimeSelector.type = { time: 'time', datetime: 'datetime-local', date: 'date' };

DateTimeSelector.propTypes = {
  choices: PropTypes.array.isRequired,
  initialChoice: PropTypes.number,
  type: PropTypes.string,
  depedencies: PropTypes.array,
};

DateTimeSelector.defaultProps = {
  initialChoice: 0,
  type: 'date',
  depedencies: [],
};

export {
  DateTimeSelector,
  today,
  addDays,
  addHours,
  setHours,
  setTimeFromString,
  setDateFromString,
};
