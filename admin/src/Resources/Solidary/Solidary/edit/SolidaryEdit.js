import React from 'react';

import {
  Edit,
  TabbedForm,
  FormTab,
  Toolbar,
  SaveButton,
  ReferenceInput,
  RadioButtonGroupInput,
  FormDataConsumer,
} from 'react-admin';

import SolidaryQuestion from '../create/SolidaryQuestion';
import SolidaryFrequency from '../create/SolidaryFrequency';
import Condition from '../../../../utils/Condition';
import GeocompleteInput from '../../../../components/geolocation/geocomplete';
import { SolidaryNeedsQuestion } from '../create/SolidaryNeedsQuestion';
import SolidaryRegularAsk from '../create/SolidaryRegularAsk';
import SolidaryPunctualAsk from '../create/SolidaryPunctualAsk';
import { addressRenderer } from '../../../../utils/renderers';
import { SolidaryPunctualAskSummary } from '../create/SolidaryPunctualAskSummary';
import Tooltip from '@material-ui/core/Tooltip';

const required = (value) => (value ? '' : 'Champs obligatoire');

const CustomToolbar = (props) => (
  <>
    <Toolbar {...props}>
      <p>Votre modification de la demande solidaire '{props.record.id}' va recréer une nouvelle demande solidaire avec les modifications attendues et clôturer l'existante</p>
    </Toolbar>
    <Toolbar {...props}>
      <SaveButton label="Recréer demande solidaire" />
    </Toolbar>
  </>
);

export const SolidaryEdit = (props) => {
  console.log('PROPS:', props);
  return (<Edit {...props} title="Éditer demande solidaire">
    <TabbedForm toolbar={<CustomToolbar />}>
      <FormTab label="Trajet">
        <SolidaryQuestion question="Que voulez-vous faire ?">
          <ReferenceInput source="subject.@id" label="Objet" reference="subjects">
            <RadioButtonGroupInput optionText="label" row />
          </ReferenceInput>
        </SolidaryQuestion>
        <SolidaryQuestion question="Où faut-il aller ?">
          <FormDataConsumer>
            {({ formData }) => (
              <GeocompleteInput
                fullWidth
                source="destination"
                label="Adresse d'arrivée"
                validate={required}
                defaultValueText={
                  formData.destination ? addressRenderer(formData.destination) : undefined
                }
              />
            )}
          </FormDataConsumer>
        </SolidaryQuestion>
        <SolidaryQuestion question="D'où devez-vous partir ?">
          <FormDataConsumer>
            {({ formData }) => (
              <GeocompleteInput
                fullWidth
                source="origin"
                label="Adresse de départ"
                validate={required}
                defaultValueText={formData.origin ? addressRenderer(formData.origin) : undefined}
              />
            )}
          </FormDataConsumer>
        </SolidaryQuestion>
        <SolidaryNeedsQuestion label="Autres informations" />
      </FormTab>
      <FormTab label="Horaires">
        <SolidaryQuestion question="Trajet ponctuel ?">
          <SolidaryFrequency source="frequency" label="ou trajet régulier ?" defaultValue={1} />
        </SolidaryQuestion>
        <Condition when="frequency" is={2 /* 2 === regular */} fallback={null}>
          <SolidaryRegularAsk
            edit
            includeNeeds={false}
            summary={<SolidaryPunctualAskSummary regularMode />}
          />
        </Condition>
        <Condition when="frequency" is={1 /* 2 === punctual */} fallback={null}>
          <SolidaryPunctualAsk edit includeNeeds={false} summary={<SolidaryPunctualAskSummary />} />
        </Condition>
      </FormTab>
    </TabbedForm>
  </Edit>);
};
