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
  ReferenceField,
} from 'react-admin';

import { usernameRenderer, solidaryJourneyRenderer } from '../../../../utils/renderers';
import isAuthorized from '../../../../auth/permissions';

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
      label="Demandeur solidaire"
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

const renderDisplayLabel = (solidary) => solidaryJourneyRenderer(solidary) || solidary.displayLabel;

export const SolidaryList = (props) => (
  <List
    {...props}
    bulkActionButtons={false}
    filters={<SolidaryFilter />}
    title="Demandes solidaires > liste"
    perPage={25}
    exporter={isAuthorized('export') ? undefined : false}
  >
    <Datagrid>
      <TextField source="originId" label="ID" />
      {/* <TextField source="subject.label" /> */}
      <SubjectField />
      <FunctionField label="Trajet demandé" render={renderDisplayLabel} />
      <TextField source="solidaryUser.user.givenName" />
      <TextField source="solidaryUser.user.familyName" />
      <FunctionField label="% avanc." render={(r) => `${r.progression}%`} />
      <ActionField source="lastAction" />
      <DateField source="createdDate" />
      <ShowButton />
      {/* @UNCOMMENT (22149) */}
      {/* <EditButton /> */}
    </Datagrid>
  </List>
);
