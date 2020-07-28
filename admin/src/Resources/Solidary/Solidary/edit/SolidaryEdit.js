import React from 'react';
import { Box } from '@material-ui/core';
import { Edit, TabbedForm, FormTab, ReferenceInput, RadioButtonGroupInput } from 'react-admin';

import SolidaryQuestion from '../create/SolidaryQuestion';
import SolidaryFrequency from '../create/SolidaryFrequency';
import Condition from '../../../../utils/Condition';
import DayChipInput from '../create/DayChipInput';
import DateIntervalSelector from '../create/DateIntervalSelector';
import { toTimeChoices, intervalChoices, fromTimeChoices } from '../create/SolidaryRegularAsk';
import { DateTimeSelector } from '../create/DateTimeSelector';

export const SolidaryEdit = (props) => {
  return (
    <Edit {...props} title="Demande Solidaire > éditer">
      <TabbedForm>
        <FormTab label="Général">
          <SolidaryQuestion question="Que voulez-vous faire ?">
            <ReferenceInput source="subject.@id" label="Objet" reference="subjects">
              <RadioButtonGroupInput optionText="label" />
            </ReferenceInput>
          </SolidaryQuestion>
          <SolidaryQuestion question="Trajet ponctuel ?">
            <SolidaryFrequency source="frequency" label="ou trajet régulier ?" defaultValue={1} />
          </SolidaryQuestion>
          <Condition when="frequency" is={2 /* 2 === regular */} fallback={null}>
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
          </Condition>
        </FormTab>
        <FormTab label="Identité">
          {/* <TextInput
        fullWidth
        required
        source="familyName"
        label={translate('custom.label.user.familyName')}
        validate={validateRequired}
        className={classes.spacedHalfwidth}
      />
      <TextInput
        fullWidth
        required
        source="givenName"
        label={translate('custom.label.user.givenName')}
        validate={validateRequired}
        className={classes.spacedHalfwidth}
      /> */}
        </FormTab>
      </TabbedForm>
    </Edit>
  );
};
