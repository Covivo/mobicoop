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
  // EditButton,
  ReferenceField,
} from 'react-admin';

import { usernameRenderer } from '../../../../utils/renderers';
import { isAdmin } from '../../../../auth/permissions';

const ActionField = ({ source, record = {} }) => {
  const translate = useTranslate();
  return <span>{translate(`custom.actions.${get(record, source)}`)}</span>;
};

const SubjectField = (props) => {
  if (typeof props.record.subject === 'string') {
    return (
      <ReferenceField {...props} source="subject" link={false} reference="subjects">
        <TextField source="label" />
      </ReferenceField>
    );
  }

  return <TextField {...props} source="subject.label" />;
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
    exporter={isAdmin()}
  >
    <Datagrid>
      <TextField source="originId" label="ID" />
      {/* <TextField source="subject.label" /> */}
      <SubjectField />
      <TextField source="displayLabel" />
      <TextField source="solidaryUser.user.givenName" />
      <TextField source="solidaryUser.user.familyName" />
      <FunctionField label="% avanc." render={(r) => `${r.progression}%`} />
      <ActionField source="lastAction" />
      <DateField source="createdDate" />
      <ShowButton />
      {/* <EditButton /> */}
    </Datagrid>
  </List>
);
