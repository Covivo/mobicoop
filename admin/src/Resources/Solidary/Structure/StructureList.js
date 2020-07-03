import React from 'react';
import { List, Datagrid, TextField, EditButton } from 'react-admin';

export const StructureList = (props) => (
  <List bulkActionButtons={false} {...props} title="Structures accompagnantes > liste">
    <Datagrid rowClick="show">
      <TextField source="name" label="Nom" />
      <EditButton />
    </Datagrid>
  </List>
);
