import React from 'react';
import { useField } from 'react-final-form';
import { DateTimeInput } from 'react-admin';
import { makeStyles } from '@material-ui/core/styles';
import DateTimeSelector from './DateTimeSelector';
import SolidaryQuestion from './SolidaryQuestion';
import SolidaryNeeds from './SolidaryNeeds';

const fromDateChoices = [
  { id: 0, label: 'A une date fixe', offsetHour: 0, offsetDays: 0 },
  { id: 1, label: 'Dans la semaine', offsetHour: 0, offsetDays: 7 },
  { id: 2, label: 'Dans la quinzaine', offsetHour: 0, offsetDays: 14 },
  { id: 3, label: 'Dans le mois', offsetHour: 0, offsetDays: 30 },
];

const fromTimeChoices = [
  { id: 0, label: 'A une heure fixe', offsetHour: 0, offsetDays: 0 },
  { id: 1, label: 'Entre 8h et 13h', offsetHour: 8, offsetDays: 0, margin: 5 * 3600 },
  { id: 2, label: 'Entre 13h et 18h', offsetHour: 13, offsetDays: 0, margin: 5 * 3600 },
  { id: 3, label: 'Entre 18h et 21h', offsetHour: 18, offsetDays: 0, margin: 3 * 3600 },
];

const toTimeChoices = [
  { id: 0, label: 'A une heure fixe', offsetHour: 0, offsetDays: 0 },
  { id: 1, label: 'Une heure plus tard', offsetHour: 1, offsetDays: 0 },
  { id: 2, label: 'Deux heures plus tard', offsetHour: 2, offsetDays: 0 },
  { id: 3, label: 'Trois heures plus tard', offsetHour: 3, offsetDays: 0 },
  { id: 4, label: "Pas besoin qu'on me ramène", offsetHour: 0, offsetDays: 0 },
];

const useStyles = makeStyles({
  invisible: { display: 'none' },
});

const SolidaryPunctualAsk = () => {
  const classes = useStyles();
  const {
    input: { value: outwardDatetime },
  } = useField('outwardDatetime');
  const {
    input: { value: outwardDeadlineDatetime },
  } = useField('outwardDeadlineDatetime');

  return (
    <>
      <div className={classes.invisible}>
        <DateTimeInput source="fromStartDate" />
      </div>
      <div className={classes.invisible}>
        <DateTimeInput source="fromEndDate" />
      </div>
      <div className={classes.invisible}>
        <DateTimeInput source="toStartDate" />
      </div>
      <div className={classes.invisible}>
        <DateTimeInput source="toEndDate" />
      </div>

      <SolidaryQuestion question="A quelle date souhaitez-vous partir ?">
        <DateTimeSelector
          type="date"
          fieldnameStart="outwardDatetime"
          fieldnameEnd="outwardDeadlineDatetime"
          choices={fromDateChoices}
          initialChoice={0}
        />
      </SolidaryQuestion>

      <SolidaryQuestion question="A quelle heure souhaitez-vous partir ?">
        <DateTimeSelector
          type="time"
          fieldnameStart="outwardDatetime"
          fieldnameEnd="outwardDeadlineDatetime"
          choices={fromTimeChoices}
          initialChoice={0}
        />
      </SolidaryQuestion>

      <SolidaryQuestion question="Quand souhaitez-vous revenir ?">
        <DateTimeSelector
          type="datetime-local"
          fieldnameStart="returnDatetime"
          fieldnameEnd="returnDeadlineDatetime"
          choices={toTimeChoices}
          initialChoice={0}
          initialStart={outwardDatetime}
          initialEnd={outwardDeadlineDatetime}
        />
      </SolidaryQuestion>

      <SolidaryQuestion question="Autres informations">
        <SolidaryNeeds />
      </SolidaryQuestion>
    </>
  );
};

export default SolidaryPunctualAsk;

/*
un demandeur souhaite partir "dans la semaine", "entre 8h et 13h" et revenir "Deux heures plus tard"  :

"outwardDatetime": "2020-05-29T08:00:00+00:00",
"outwardDeadlineDatetime": "2020-06-05T08:00:00+00:00",
"returnDatetime": "2020-05-29T10:00:00+00:00",
"returnDeadlineDatetime": "2020-06-05T10:00:00+00:00", 
"margin":"18000" // s'applique à l'aller et au retour 5h * 3600s

Pour un régulier

"outwardDatetime": "2020-05-29T08:00:00+00:00",       -> début
"outwardDeadlineDatetime": "2020-06-05T08:00:00+00:00",-> fin
days: []
"returnDatetime": "2020-05-29T10:00:00+00:00",  --> heure du retour
"returnDeadlineDatetime": "2020-06-05T10:00:00+00:00", 

*/
