import React from 'react';
import { List, Datagrid, TextField, ReferenceField, ShowButton, EditButton } from 'react-admin';

import { defaultExporterFunctionSuperAdmin } from '../../utils/utils';

export const RoleList = (props) => (
  <List {...props} title="Rôles > liste" exporter={defaultExporterFunctionSuperAdmin()} perPage={25}>
    <Datagrid>
      <TextField source="originId" label="ID" sortBy="id" />
      <TextField source="title" label="Titre" />
      <TextField source="name" label="Nom" />
      <ReferenceField
        source="parent"
        label="Rôle parent"
        reference="roles"
        allowEmpty
        sortable={false}
      >
        <TextField source="title" />
      </ReferenceField>
      <ShowButton />
      <EditButton />
    </Datagrid>
  </List>
);
