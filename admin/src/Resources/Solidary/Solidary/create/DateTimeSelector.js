import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import { FormControlLabel, RadioGroup, Radio, Box, TextField } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { useField } from 'react-final-form';
import { utcDateFormat } from '../../../../utils/date';

const formatDate = (d) => utcDateFormat(d, "yyyy'-'MM'-'dd");
const formatHour = (d) => utcDateFormat(d, 'HH:mm');

const useStyles = makeStyles({
  invisible: { display: 'none' },
  dateControlWitdh: { width: '200px' },
});

/*
const fromDateChoices = [
  { id: 0, label: 'A une date fixe', outwardDatetime : ({selectedDateTime}) => selectedDateTime },
  { id: 1, label: 'Dans la semaine', outwardDatetime : () => today(), outwardDeadlineDatetime : () => addDays(today, 7) },
  { id: 2, label: 'Dans la quinzaine', outwardDatetime : () => today(), outwardDeadlineDatetime : () => addDays(today,14) },
  { id: 3, label: 'Dans le mois', outwardDatetime : () => today(), outwardDeadlineDatetime : () => addDays(today,30) },
];

const fromTimeChoices = [
  { id: 0, label: 'A une heure fixe', outwardDatetime : ({selectedDateTime}) => selectedDateTime },
  { id: 1, label: 'Entre 8h et 13h', outwardDatetime : ({outwardDatetime}) => setHours(outwardDatetime, 8),  marginDuration: () => 5 * 3600 },
  { id: 2, label: 'Entre 13h et 18h', outwardDatetime : ({outwardDatetime}) => setHours(outwardDatetime, 13), marginDuration: () => 5 * 3600 },
  { id: 3, label: 'Entre 18h et 21h', outwardDatetime : ({outwardDatetime}) => setHours(outwardDatetime, 18), marginDuration: () => 3 * 3600 },
];

const toTimeChoices = [
  { id: 0, label: 'A une heure fixe',  returnDatetime : ({selectedDateTime}) => selectedDateTime },
  { id: 1, label: 'Une heure plus tard', returnDatetime : ({outwardDatetime}) => addHours(outwardDatetime, 1) },
  { id: 2, label: 'Deux heures plus tard',  returnDatetime : ({outwardDatetime}) => addHours(outwardDatetime,2)  },
  { id: 3, label: 'Trois heures plus tard',  returnDatetime : ({outwardDatetime}) => addHours(outwardDatetime,3)  },
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

const setHours = (originDate, hours, minutes) => {
  const alteredDate = new Date(originDate);
  alteredDate.setHours(hours || 0);
  alteredDate.setMinutes(minutes || 0);
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
const DateTimeSelector = ({
  choices,
  initialChoice,
  initialValue,
  dependencies,
  type = 'date',
  edit,
}) => {
  const classes = useStyles();
  const [choice, setChoice] = useState(choices[initialChoice]);
  const [selectedDateTime, setSelectedDateTime] = useState(null);

  const {
    input: { value: outwardDatetime, onChange: onChangeOutwardDateTime },
  } = useField('outwardDatetime');

  const {
    input: { value: outwardDeadlineDatetime, onChange: onChangeOutwardDeadlineDateTime },
  } = useField('outwardDeadlineDatetime');

  const {
    input: { value: returnDatetime, onChange: onChangeReturnDateTime },
  } = useField('returnDatetime');

  const {
    input: { value: returnDeadlineDatetime, onChange: onChangeReturnDeadlineDateTime },
  } = useField('returnDeadlineDatetime');

  const {
    input: { value: marginDuration, onChange: onChangeMarginDuration },
  } = useField('marginDuration');

  const parameters = {
    outwardDatetime,
    outwardDeadlineDatetime,
    returnDatetime,
    marginDuration,
    selectedDateTime,
  };

  useEffect(() => {
    // Set datetime fields according to choice and selectedDateTime

    if (choice.outwardDatetime) {
      onChangeOutwardDateTime(choice.outwardDatetime(parameters));
    }

    if (choice.outwardDeadlineDatetime) {
      onChangeOutwardDeadlineDateTime(choice.outwardDeadlineDatetime(parameters));
    }

    if (choice.returnDatetime) {
      onChangeReturnDateTime(choice.returnDatetime(parameters));
    }

    if (choice.returnDeadlineDatetime) {
      onChangeReturnDeadlineDateTime(choice.returnDeadlineDatetime(parameters));
    }

    if (choice.marginDuration) {
      onChangeMarginDuration(choice.marginDuration(parameters));
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [choice, selectedDateTime && selectedDateTime.toString(), ...dependencies]);

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
            InputLabelProps={{ shrink: true }}
            onChange={(e) => setSelectedDateTime(e.target.value)}
            className={classes.dateControlWitdh}
            defaultValue={
              edit && initialValue
                ? type === 'date'
                  ? formatDate(initialValue)
                  : formatHour(initialValue)
                : null
            }
          />
        </div>
      </Box>
    </Box>
  );
};

DateTimeSelector.type = { time: 'time', date: 'date' };

DateTimeSelector.propTypes = {
  choices: PropTypes.array.isRequired,
  initialChoice: PropTypes.number,
  type: PropTypes.string,
  dependencies: PropTypes.array,
  edit: PropTypes.bool,
};

DateTimeSelector.defaultProps = {
  initialChoice: 0,
  type: DateTimeSelector.type.date,
  dependencies: [],
  edit: false,
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
