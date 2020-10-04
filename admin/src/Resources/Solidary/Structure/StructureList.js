import React from 'react';
import { List, Datagrid, TextField, EditButton } from 'react-admin';

import { defaultExporterFunctionAdmin } from '../../../utils/utils';

export const StructureList = (props) => (
  <List
    bulkActionButtons={false}
    {...props}
    exporter={defaultExporterFunctionAdmin()}
    title="Structures accompagnantes > liste"
  >
    <Datagrid rowClick="show">
      <TextField source="name" label="Nom" />
      <EditButton />
    </Datagrid>
  </List>
);
