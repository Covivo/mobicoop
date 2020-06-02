import React from 'react';
import PropTypes from 'prop-types';
import Box from '@material-ui/core/Box';
import { DateTimeSelector } from './DateTimeSelector';
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

/*
Important :

- Le champ destination est optionnel.
- S’il ne s’agit que d’un aller sans retour ne pas indiquer returnDatetime et
returnDeadlineDatetime

- Ne pas indiquer outwardDeadlineDatetime et returnDeadlineDatetime s’il s’agit
d’un jour fixe. (l’indiquer s’il s’agit d’un régulier ou
si l’utilisateur à indiqué ‘dans la semaine’)

- S’il s’agit d’un
trajet régulier (frequency = 2) indiquer days.

Attention :
- marginDuration
indique la marge en secondes de l’heure de départ. Si un
utilisateur sélectionne départ entre 8h et 13h. Indiquer comme
heure de départ dans originDatetime 10h30 et comme
marginDuration 2h30 soit 9000 secondes. Si un utilisateur
indique une heure précise ne pas renseigner marginDuration.
*/

const SolidaryRegularAsk = ({ form }) => {
  return (
    <>
      <SolidaryQuestion question="Quels jours devez-vous voyager ?">
        <Box>
          <DayChipInput source="days.mon" label="L" form={form} />
          <DayChipInput source="days.tue" label="Ma" form={form} />
          <DayChipInput source="days.wed" label="Me" form={form} />
          <DayChipInput source="days.thu" label="J" form={form} />
          <DayChipInput source="days.fri" label="V" form={form} />
          <DayChipInput source="days.sat" label="S" form={form} />
          <DayChipInput source="days.sun" label="D" form={form} />
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
          initialChoice={0}
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
