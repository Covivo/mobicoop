import React from 'react';
import get from 'lodash.get';

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
  useTranslate,
} from 'react-admin';

import { usernameRenderer } from '../../../../utils/renderers';

const ActionField = ({ source, record = {} }) => {
  const translate = useTranslate();
  return <span>{translate(`custom.actions.${get(record, source)}`)}</span>;
};

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
      <ActionField source="lastAction" />
      <DateField source="createdDate" />
      <ShowButton />
    </Datagrid>
  </List>
);
