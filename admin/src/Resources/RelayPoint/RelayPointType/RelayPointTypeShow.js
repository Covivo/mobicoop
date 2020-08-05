import React from 'react';

import {
  Show,
  SimpleShowLayout,
  TextField,
  EditButton,
  ReferenceField,
  ImageField,
} from 'react-admin';

export const RelayPointTypeShow = (props) => (
  <Show {...props} title="Types de points relais > afficher">
    <SimpleShowLayout>
      <TextField source="originId" label="ID" />
      <TextField source="name" label="Nom" />
      <ReferenceField source="icon.id" label="Icone" reference="icons">
        <ImageField label="Icon" source="url" />
      </ReferenceField>
      <EditButton />
    </SimpleShowLayout>
  </Show>
);
