import React from 'react';
import { List, Datagrid, TextField, EditButton } from 'react-admin';

import isAuthorized from '../../../auth/permissions';

export const StructureList = (props) => (
  <List
    bulkActionButtons={false}
    {...props}
    exporter={isAuthorized('export') ? undefined : false}
    title="Structures accompagnantes > liste"
  >
    <Datagrid rowClick="show">
      <TextField source="name" label="Nom" />
      <EditButton />
    </Datagrid>
  </List>
);
