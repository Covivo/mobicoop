import React from 'react';

import {
  List,
  Datagrid,
  TextField,
  ShowButton,
  FunctionField,
  DateField,
  ReferenceInput,
  AutocompleteInput,
  Filter,
} from 'react-admin';

import { usernameRenderer } from '../../../../utils/renderers';

const SolidaryFilter = (props) => (
  <Filter {...props}>
    <ReferenceInput
      alwaysOn
      fullWidth
      label="User solidaire"
      source="solidaryUser"
      reference="solidary_users"
    >
      <AutocompleteInput
        allowEmpty
        optionText={(record) => (record.user ? usernameRenderer({ record: record.user }) : '')}
      />
    </ReferenceInput>
  </Filter>
);

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
