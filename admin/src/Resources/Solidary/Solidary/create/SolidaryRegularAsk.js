import React, { useEffect } from 'react';
import PropTypes from 'prop-types';
import Box from '@material-ui/core/Box';
import { useField } from 'react-final-form';
import { differenceInSeconds, addSeconds } from 'date-fns';

import { DateTimeSelector, setHours, addHours, setTimeFromString } from './DateTimeSelector';
import SolidaryQuestion from './SolidaryQuestion';
import DayChipInput from './DayChipInput';
import DateIntervalSelector from './DateIntervalSelector';
import { SolidaryNeedsQuestion } from './SolidaryNeedsQuestion';
import SolidaryRegularSchedules from './SolidaryRegularSchedules';

export const regularIntervalChoices = [
  { id: 0, label: 'Sur une période fixe' },
  { id: 1, label: 'Pendant une semaine', offsetDays: 7, offsetMonth: 0 },
  { id: 2, label: 'Pendant un mois', offsetDays: 0, offsetMonth: 1 },
];

export const regularFromTimeChoices = [
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

export const regularToTimeChoices = [
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

export const regularTimeChoices = [
  {
    id: 0,
    returnDatetime: ({ outwardDatetime, selectedDateTime }) =>
      setTimeFromString(outwardDatetime, selectedDateTime),
  },
  { id: 1, returnDatetime: () => null },
];

const castDate = (date) => (typeof date === 'string' ? new Date(date) : date);

const SolidaryRegularAsk = ({ includeNeeds = true, summary = null }) => {
  const {
    input: { value: outwardDatetime },
  } = useField('outwardDatetime');

  const {
    input: { value: returnDatetime },
  } = useField('returnDatetime');

  const {
    input: { value: outwardDeadlineDatetime },
  } = useField('outwardDeadlineDatetime');

  const {
    input: { onChange: onChangeReturnDeadlineDatetime },
  } = useField('returnDeadlineDatetime');

  useEffect(() => {
    const secondsDiff = differenceInSeconds(castDate(returnDatetime), castDate(outwardDatetime));

    onChangeReturnDeadlineDatetime(
      secondsDiff > 0
        ? addSeconds(outwardDeadlineDatetime, secondsDiff)
        : setTimeFromString(outwardDeadlineDatetime, '00:00')
    );
  }, [JSON.stringify({ returnDatetime, outwardDatetime, outwardDeadlineDatetime })]);

  return (
    <Box display="flex">
      <Box flex={3} mr="1em">
        <SolidaryQuestion question="Quels jours devez-vous voyager ?">
          <SolidaryRegularSchedules choices={regularTimeChoices} initialChoice={1} />
        </SolidaryQuestion>
        <SolidaryQuestion question="Pendant combien de temps devez-vous faire ce trajet ?">
          <DateIntervalSelector
            type="date"
            fieldnameStart="outwardDatetime"
            fieldnameEnd="outwardDeadlineDatetime"
            choices={regularIntervalChoices}
            initialChoice={0}
          />
        </SolidaryQuestion>
        {includeNeeds && <SolidaryNeedsQuestion label="Autres informations" />}
      </Box>
      {summary && <Box flex={1}>{summary}</Box>}
    </Box>
  );
};

export default SolidaryRegularAsk;
