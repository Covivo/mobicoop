import React from 'react';

import {
  List,
  Datagrid,
  EditButton,
  TextField,
  ReferenceManyField,
  ChipField,
  SingleFieldList,
  SelectField,
} from 'react-admin';

import { isAdmin, isSuperAdmin } from '../../../auth/permissions';

const statusChoices = [
  { id: 0, name: "En cours d'édition" },
  { id: 1, name: 'En ligne' },
];

export const ArticleList = (props) => (
  <List
    {...props}
    title="Articles > liste"
    exporter={isSuperAdmin()}
    perPage={25}
    sort={{ field: 'originId', order: 'ASC' }}
  >
    <Datagrid rowClick="show">
      <TextField source="originId" label="ID" sortBy="id" />
      <TextField source="title" label="Titre" />
      <SelectField source="status" label="Status" choices={statusChoices} />
      <ReferenceManyField label="Sections" reference="sections" target="article" sortable={false}>
        <SingleFieldList linkType="show">
          <ChipField source="title" />
        </SingleFieldList>
      </ReferenceManyField>
      <EditButton />
    </Datagrid>
  </List>
);
