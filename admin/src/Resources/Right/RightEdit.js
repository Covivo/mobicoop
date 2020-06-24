import React from 'react';

import { Edit, SimpleForm, TextInput, SelectInput, ReferenceInput, required } from 'react-admin';

const typeChoices = [
  { id: 1, name: 'Item' },
  { id: 2, name: 'Groupe' },
];

export const RightEdit = (props) => (
  <Edit {...props} title="Droits > éditer">
    <SimpleForm>
      <TextInput disabled source="originId" label="ID" />
      <SelectInput source="type" label="Type" choices={typeChoices} validate={required()} />
      <TextInput source="name" label="Nom" validate={required()} />
      <ReferenceInput source="parent" label="Groupe" reference="rights" filter={{ type: 2 }}>
        <SelectInput optionText="name" />
      </ReferenceInput>
    </SimpleForm>
  </Edit>
);
