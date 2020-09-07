import React from 'react';
import { useField } from 'react-final-form';
import { Box } from '@material-ui/core';

import {
  DateTimeSelector,
  today,
  addDays,
  setHours,
  addHours,
  setTimeFromString,
  setDateFromString,
} from './DateTimeSelector';

import SolidaryQuestion from './SolidaryQuestion';
import { SolidaryNeedsQuestion } from './SolidaryNeedsQuestion';
import { format } from 'date-fns';
import { toUTC } from '../../../../utils/date';

export const punctualFromDateChoices = [
  {
    id: 0,
    label: 'A une date fixe',
    outwardDatetime: ({ outwardDatetime, selectedDateTime }) =>
      setDateFromString(outwardDatetime, selectedDateTime),
    outwardDeadlineDatetime: () => null,
  },
  {
    id: 1,
    label: 'Dans la semaine',
    outwardDatetime: ({ outwardDatetime }) => setDateFromString(outwardDatetime, today), // preserve hours
    outwardDeadlineDatetime: () => addDays(today, 7),
  },
  {
    id: 2,
    label: 'Dans la quinzaine',
    outwardDatetime: ({ outwardDatetime }) => setDateFromString(outwardDatetime, today),
    outwardDeadlineDatetime: () => addDays(today, 14),
  },
  {
    id: 3,
    label: 'Dans le mois',
    outwardDatetime: ({ outwardDatetime }) => setDateFromString(outwardDatetime, today),
    outwardDeadlineDatetime: () => addDays(today, 30),
  },
];

export const punctualFromTimeChoices = [
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

export const punctualToTimeChoices = [
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

const SolidaryPunctualAsk = ({ includeNeeds, summary = null }) => {
  const {
    input: { value: outwardDatetime },
  } = useField('outwardDatetime');

  return (
    <Box display="flex">
      <Box flex={3} mr="1em">
        <SolidaryQuestion question="A quelle date souhaitez-vous partir ?">
          <DateTimeSelector type="date" choices={punctualFromDateChoices} initialChoice={0} />
        </SolidaryQuestion>
        <SolidaryQuestion question="A quelle heure souhaitez-vous partir ?">
          <DateTimeSelector type="time" choices={punctualFromTimeChoices} initialChoice={0} />
        </SolidaryQuestion>
        <SolidaryQuestion question="Quand souhaitez-vous revenir ?">
          <DateTimeSelector
            type="time"
            choices={punctualToTimeChoices}
            initialChoice={4}
            dependencies={[outwardDatetime]}
          />
        </SolidaryQuestion>
        {includeNeeds && <SolidaryNeedsQuestion label="Autres informations" />}
      </Box>
      {summary && <Box flex={1}>{summary}</Box>}
    </Box>
  );
};

export default SolidaryPunctualAsk;
