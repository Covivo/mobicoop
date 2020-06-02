import React from 'react';
import { useField } from 'react-final-form';
import { Box } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
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
import SolidaryNeeds from './SolidaryNeeds';

const fromDateChoices = [
  {
    id: 0,
    label: 'A une date fixe',
    outwardDateTime: ({ outwardDateTime, selectedDateTime }) =>
      setDateFromString(outwardDateTime, selectedDateTime),
    outwardDeadlineDateTime: () => null,
  },
  {
    id: 1,
    label: 'Dans la semaine',
    outwardDateTime: ({ outwardDateTime }) => setDateFromString(outwardDateTime, today), // preserve hours
    outwardDeadlineDateTime: () => addDays(today, 7),
  },
  {
    id: 2,
    label: 'Dans la quinzaine',
    outwardDateTime: ({ outwardDateTime }) => setDateFromString(outwardDateTime, today),
    outwardDeadlineDateTime: () => addDays(today, 14),
  },
  {
    id: 3,
    label: 'Dans le mois',
    outwardDateTime: ({ outwardDateTime }) => setDateFromString(outwardDateTime, today),
    outwardDeadlineDateTime: () => addDays(today, 30),
  },
];

const fromTimeChoices = [
  {
    id: 0,
    label: 'A une heure fixe',
    outwardDateTime: ({ selectedDateTime, outwardDateTime }) =>
      setTimeFromString(outwardDateTime, selectedDateTime),
  },
  {
    id: 1,
    label: 'Entre 8h et 13h',
    outwardDateTime: ({ outwardDateTime }) => setHours(outwardDateTime, 8),
    marginDuration: () => 5 * 3600,
  },
  {
    id: 2,
    label: 'Entre 13h et 18h',
    outwardDateTime: ({ outwardDateTime }) => setHours(outwardDateTime, 13),
    marginDuration: () => 5 * 3600,
  },
  {
    id: 3,
    label: 'Entre 18h et 21h',
    outwardDateTime: ({ outwardDateTime }) => setHours(outwardDateTime, 18),
    marginDuration: () => 3 * 3600,
  },
];

const toTimeChoices = [
  {
    id: 0,
    label: 'A une heure fixe',
    returnDateTime: ({ outwardDateTime, selectedDateTime }) =>
      setTimeFromString(outwardDateTime, selectedDateTime),
  },
  {
    id: 1,
    label: 'Une heure plus tard',
    returnDateTime: ({ outwardDateTime }) => addHours(outwardDateTime, 1),
  },
  {
    id: 2,
    label: 'Deux heures plus tard',
    returnDateTime: ({ outwardDateTime }) => addHours(outwardDateTime, 2),
  },
  {
    id: 3,
    label: 'Trois heures plus tard',
    returnDateTime: ({ outwardDateTime }) => addHours(outwardDateTime, 3),
  },
  { id: 4, label: "Pas besoin qu'on me ramène", returnDateTime: () => null },
];

const useStyles = makeStyles({
  invisible: { display: 'block' },
});

const SolidaryPunctualAsk = () => {
  const classes = useStyles();
  const {
    input: { value: outwardDateTime },
  } = useField('outwardDateTime');
  const {
    input: { value: outwardDeadlineDateTime },
  } = useField('outwardDeadlineDateTime');
  const {
    input: { value: returnDateTime },
  } = useField('returnDateTime');
  const {
    input: { value: marginDuration },
  } = useField('marginDuration');

  return (
    <Box display="flex">
      <Box flex={3} mr="1em">
        <SolidaryQuestion question="A quelle date souhaitez-vous partir ?">
          <DateTimeSelector type="date" choices={fromDateChoices} initialChoice={0} />
        </SolidaryQuestion>

        <SolidaryQuestion question="A quelle heure souhaitez-vous partir ?">
          <DateTimeSelector type="time" choices={fromTimeChoices} initialChoice={0} />
        </SolidaryQuestion>

        <SolidaryQuestion question="Quand souhaitez-vous revenir ?">
          <DateTimeSelector
            type="datetime-local"
            choices={toTimeChoices}
            initialChoice={4}
            depedencies={[outwardDateTime]}
          />
        </SolidaryQuestion>

        <SolidaryQuestion question="Autres informations">
          <SolidaryNeeds />
        </SolidaryQuestion>
      </Box>
      <Box flex={1}>
        <SolidaryQuestion question="Récapitulatif">
          <p>Départ : {outwardDateTime ? outwardDateTime.toLocaleString() : 'Pas de date'}</p>
          <p>
            Départ limite:{' '}
            {outwardDeadlineDateTime ? outwardDeadlineDateTime.toLocaleString() : 'Pas de date'}
          </p>
          <p>Retour : {returnDateTime ? returnDateTime.toLocaleString() : 'Pas de date'}</p>
          <p>
            Marge :{' '}
            {marginDuration ? `${Math.round(marginDuration / 3600)} heures` : 'Pas de marge'}
          </p>
        </SolidaryQuestion>
      </Box>
    </Box>
  );
};

export default SolidaryPunctualAsk;

/*
un demandeur souhaite partir "dans la semaine", "entre 8h et 13h" et revenir "Deux heures plus tard"  :

"outwardDatetime": "2020-05-29T08:00:00+00:00",
"outwardDeadlineDatetime": "2020-06-05T08:00:00+00:00",
"returnDatetime": "2020-05-29T10:00:00+00:00",
"returnDeadlineDatetime": "2020-06-05T10:00:00+00:00", 
"marginDuration":"18000" // s'applique à l'aller et au retour 5h * 3600s

Pour un régulier

"outwardDatetime": "2020-05-29T08:00:00+00:00",       -> début
"outwardDeadlineDatetime": "2020-06-05T08:00:00+00:00",-> fin
days: []
"returnDatetime": "2020-05-29T10:00:00+00:00",  --> heure du retour
"returnDeadlineDatetime": "2020-06-05T10:00:00+00:00", 

*/
