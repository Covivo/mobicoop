import React from 'react';
import { List, Datagrid, TextField, EditButton } from 'react-admin';

import { isAdmin, isSuperAdmin } from '../../auth/permissions';

export const TerritoryList = (props) => (
  <List {...props} title="Territoires > liste" exporter={isSuperAdmin()} perPage={25}>
    <Datagrid rowClick="show">
      <TextField source="originId" label="ID" sortBy="id" />
      <TextField source="name" label="Nom" />
      <EditButton />
    </Datagrid>
  </List>
);
