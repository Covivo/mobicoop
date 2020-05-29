import React from 'react';
import PropTypes from 'prop-types';
import Box from '@material-ui/core/Box';
import DateTimeSelector from './DateTimeSelector';
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
  { id: 0, label: 'A une heure fixe', offsetHour: 0, offsetDays: 0 },
  { id: 1, label: 'Entre 8h et 13h', offsetHour: 5, offsetDays: 0, fromHour: 8 },
  { id: 2, label: 'Entre 13h et 18h', offsetHour: 5, offsetDays: 0, fromHour: 13 },
  { id: 3, label: 'Entre 18h et 21h', offsetHour: 3, offsetDays: 0, fromHour: 18 },
];

const toTimeChoices = [
  { id: 0, label: 'A une heure fixe', offsetHour: 0, offsetDays: 0 },
  { id: 1, label: 'Une heure plus tard', offsetHour: 1, offsetDays: 0 },
  { id: 2, label: 'Deux heures plus tard', offsetHour: 2, offsetDays: 0 },
  { id: 3, label: 'Trois heures plus tard', offsetHour: 3, offsetDays: 0 },
  { id: 4, label: "Pas besoin qu'on me ramène", offsetHour: 0, offsetDays: 0 },
];

const SolidaryRegularAsk = ({ form }) => {
  return (
    <>
      <SolidaryQuestion question="Quels jours devez-vous voyager ?">
        <Box>
          <DayChipInput source="monCheck" label="L" form={form} />
          <DayChipInput source="tueCheck" label="Ma" form={form} />
          <DayChipInput source="wedCheck" label="Me" form={form} />
          <DayChipInput source="thuCheck" label="J" form={form} />
          <DayChipInput source="friCheck" label="V" form={form} />
          <DayChipInput source="satCheck" label="S" form={form} />
          <DayChipInput source="sunCheck" label="D" form={form} />
        </Box>
      </SolidaryQuestion>

      <SolidaryQuestion question="A quelle heure souhaitez-vous partir ?">
        <DateTimeSelector
          form={form}
          type="time"
          fieldnameStart="fromStartDate"
          fieldnameEnd="fromEndDate"
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
          initialChoice={0}
        />
      </SolidaryQuestion>

      <SolidaryQuestion question="Pendant combien de temps devez-vous faire ce trajet ?">
        <DateIntervalSelector
          type="date"
          fieldnameStart="intervalStartDate"
          fieldnameEnd="intervalEndDate"
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
