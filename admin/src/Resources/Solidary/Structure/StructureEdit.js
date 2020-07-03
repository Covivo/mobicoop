import React from 'react';
import { Edit, required, TextInput, SimpleForm } from 'react-admin';

export const StructureEdit = (props) => (
  <Edit {...props} title="Structures accompagnantes > éditer">
    <SimpleForm>
      <TextInput source="name" label="Nom" validate={required()} />
    </SimpleForm>
  </Edit>
);
