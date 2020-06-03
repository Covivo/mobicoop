import React from 'react';

import { List, Datagrid, TextField, EditButton } from 'react-admin';

export const SolidaryList = (props) => (
  <List {...props} title="Demandes solidaires > liste" perPage={25}>
    <Datagrid>
      <TextField source="name" label="Nom" />
      <EditButton />
    </Datagrid>
  </List>
);
