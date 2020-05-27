import React from 'react';
import { Edit, TabbedForm, FormTab } from 'react-admin';

import { AvailabilityGridInput } from './Input/AvailabilityGridInput';
import { ValidateCandidateInput } from './Input/ValidateCandidateInput';
import { UserInformationField } from './Fields/UserInformationField';

export const SolidaryUserVolunteerEdit = (props) => (
  <Edit {...props} title="Transporteurs Bénévoles > ajouter">
    <TabbedForm>
      <FormTab label="custom.solidary_volunteers.edit.availability">
        <UserInformationField />
        <AvailabilityGridInput />
      </FormTab>
      <FormTab label="custom.solidary_volunteers.edit.planning">
        {/* @TODO: FILL THIS FORM PART */}
      </FormTab>
      <FormTab label="custom.solidary_volunteers.edit.submission">
        <ValidateCandidateInput source="validatedCandidate" />
      </FormTab>
    </TabbedForm>
  </Edit>
);
