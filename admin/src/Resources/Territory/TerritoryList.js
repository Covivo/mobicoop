import React from 'react';
import { List, Datagrid, TextField, EditButton } from 'react-admin';

import { isAdmin } from '../../auth/permissions';

export const TerritoryList = (props) => (
  <List {...props} title="Territoires > liste" exporter={isAdmin()} perPage={25}>
    <Datagrid rowClick="show">
      <TextField source="originId" label="ID" sortBy="id" />
      <TextField source="name" label="Nom" />
      <EditButton />
    </Datagrid>
  </List>
);
