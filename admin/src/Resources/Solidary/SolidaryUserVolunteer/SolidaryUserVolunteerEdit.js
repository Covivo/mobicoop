import React from 'react';
import { Edit, TabbedForm, FormTab, TextInput, required } from 'react-admin';

export const SolidaryUserVolunteerEdit = (props) => (
  <Edit {...props} title="Transporteurs Bénévoles > ajouter">
    <TabbedForm>
      <FormTab label="custom.solidary_volunteers.edit.availability">
        <TextInput source="givenName" validate={required()} />
      </FormTab>
      <FormTab label="custom.solidary_volunteers.edit.planning">
        {/* @TODO: FILL THIS FORM PART */}
      </FormTab>
      <FormTab label="custom.solidary_volunteers.edit.submission">
        {/* @TODO: FILL THIS FORM PART */}
      </FormTab>
    </TabbedForm>
  </Edit>
);
