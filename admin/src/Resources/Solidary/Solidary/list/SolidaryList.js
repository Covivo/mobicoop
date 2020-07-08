import React from 'react';

import {
  List,
  Datagrid,
  TextField,
  ShowButton,
  FunctionField,
  DateField,
  Filter,
} from 'react-admin';

const SolidaryFilter = (props) => <Filter {...props}>{/* TODO: See questions */}</Filter>;

export const SolidaryList = (props) => (
  <List
    {...props}
    bulkActionButtons={false}
    filters={<SolidaryFilter />}
    title="Demandes solidaires > liste"
    perPage={25}
  >
    <Datagrid>
      <TextField source="originId" label="ID" />
      <TextField source="subject.label" />
      <TextField source="displayLabel" />
      <TextField source="solidaryUser.user.givenName" />
      <TextField source="solidaryUser.user.familyName" />
      <FunctionField label="% avanc." render={(r) => `${r.progression}%`} />
      <TextField source="lastAction" />
      <DateField source="createdDate" />
      <ShowButton />
    </Datagrid>
  </List>
);
