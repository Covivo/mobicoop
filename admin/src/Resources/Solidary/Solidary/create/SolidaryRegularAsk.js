import React from 'react';
import PropTypes from 'prop-types';
import Box from '@material-ui/core/Box';
import { DateTimeSelector, setHours, addHours, setTimeFromString } from './DateTimeSelector';
import SolidaryQuestion from './SolidaryQuestion';
import SolidaryNeeds from './SolidaryNeeds';
import DayChipInput from './DayChipInput';
import DateIntervalSelector from './DateIntervalSelector';

const intervalChoices = [
  { id: 0, label: 'Sur une période fixe' },
  { id: 1, label: 'Pendant une semaine', offsetDays: 7, offsetMonth: 0 },
  { id: 2, label: 'Pendant un mois', offsetDays: 0, offsetMonth: 1 },
];

const fromTimeChoices = [
  {
    id: 0,
    label: 'A une heure fixe',
    outwardDatetime: ({ selectedDateTime, outwardDatetime }) =>
      setTimeFromString(outwardDatetime, selectedDateTime),
    marginDuration: () => null,
  },
  {
    id: 1,
    label: 'Entre 8h et 13h',
    outwardDatetime: ({ outwardDatetime }) => setHours(outwardDatetime, 8),
    marginDuration: () => 5 * 3600,
  },
  {
    id: 2,
    label: 'Entre 13h et 18h',
    outwardDatetime: ({ outwardDatetime }) => setHours(outwardDatetime, 13),
    marginDuration: () => 5 * 3600,
  },
  {
    id: 3,
    label: 'Entre 18h et 21h',
    outwardDatetime: ({ outwardDatetime }) => setHours(outwardDatetime, 18),
    marginDuration: () => 3 * 3600,
  },
];

const toTimeChoices = [
  {
    id: 0,
    label: 'A une heure fixe',
    returnDatetime: ({ outwardDatetime, selectedDateTime }) =>
      setTimeFromString(outwardDatetime, selectedDateTime),
  },
  {
    id: 1,
    label: 'Une heure plus tard',
    returnDatetime: ({ outwardDatetime }) => addHours(outwardDatetime, 1),
  },
  {
    id: 2,
    label: 'Deux heures plus tard',
    returnDatetime: ({ outwardDatetime }) => addHours(outwardDatetime, 2),
  },
  {
    id: 3,
    label: 'Trois heures plus tard',
    returnDatetime: ({ outwardDatetime }) => addHours(outwardDatetime, 3),
  },
  { id: 4, label: "Pas besoin qu'on me ramène", returnDatetime: () => null },
];

const SolidaryRegularAsk = ({ form }) => {
  return (
    <>
      <SolidaryQuestion question="Quels jours devez-vous voyager ?">
        <Box>
          <DayChipInput source="days.mon" label="L" />
          <DayChipInput source="days.tue" label="Ma" />
          <DayChipInput source="days.wed" label="Me" />
          <DayChipInput source="days.thu" label="J" />
          <DayChipInput source="days.fri" label="V" />
          <DayChipInput source="days.sat" label="S" />
          <DayChipInput source="days.sun" label="D" />
        </Box>
      </SolidaryQuestion>

      <SolidaryQuestion question="A quelle heure souhaitez-vous partir ?">
        <DateTimeSelector
          form={form}
          type="time"
          fieldnameStart="outwardDatetime"
          fieldnameEnd="marginDuration"
          fieldMarginDuration
          choices={fromTimeChoices}
          initialChoice={0}
        />
      </SolidaryQuestion>

      <SolidaryQuestion question="Quand souhaitez-vous revenir ?">
        <DateTimeSelector
          form={form}
          type="time"
          fieldnameStart="toStartDatetime"
          fieldnameEnd="toEndDatetime"
          choices={toTimeChoices}
          initialChoice={4}
        />
      </SolidaryQuestion>

      <SolidaryQuestion question="Pendant combien de temps devez-vous faire ce trajet ?">
        <DateIntervalSelector
          type="date"
          fieldnameStart="outwardDatetime"
          fieldnameEnd="outwardDeadlineDatetime"
          choices={intervalChoices}
          initialChoice={0}
        />
      </SolidaryQuestion>

      <SolidaryQuestion question="Autres informations">
        <SolidaryNeeds />
      </SolidaryQuestion>
    </>
  );
};

SolidaryRegularAsk.propTypes = {
  form: PropTypes.object.isRequired,
};

export default SolidaryRegularAsk;
