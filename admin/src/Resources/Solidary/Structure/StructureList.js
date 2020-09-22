import React from 'react';
import { List, Datagrid, TextField, EditButton } from 'react-admin';

import { isAdmin, isSuperAdmin } from '../../../auth/permissions';

export const StructureList = (props) => (
  <List
    bulkActionButtons={false}
    {...props}
    exporter={isAdmin()}
    title="Structures accompagnantes > liste"
  >
    <Datagrid rowClick="show">
      <TextField source="name" label="Nom" />
      <EditButton />
    </Datagrid>
  </List>
);
