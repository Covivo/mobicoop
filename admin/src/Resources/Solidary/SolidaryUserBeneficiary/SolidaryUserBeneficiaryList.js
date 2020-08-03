import React from 'react';

import {
  List,
  Datagrid,
  TextField,
  TextInput,
  EditButton,
  Filter,
  NullableBooleanInput,
} from 'react-admin';

import { PhoneField } from './Fields/PhoneField';
import { YesNoField } from './Fields/YesNoField';
import { isAdmin } from '../../../auth/permissions';

const SolidaryUserBeneficiaryFilter = (props) => (
  <Filter {...props}>
    <TextInput source="givenName" alwaysOn />
    <TextInput source="familyName" alwaysOn />
    <NullableBooleanInput
      alwaysOn
      displayNull
      label="custom.solidary_beneficiaries.input.validatedCandidate"
      source="validatedCandidate"
      choices={[{ id: false, name: 'Candidats' }]}
    />
  </Filter>
);

export const SolidaryUserBeneficiaryList = (props) => (
  <List
    {...props}
    bulkActionButtons={false}
    filters={<SolidaryUserBeneficiaryFilter />}
    title="Demandeurs solidaires > liste"
    perPage={25}
    exporter={isAdmin()}
  >
    <Datagrid>
      <TextField source="originId" label="ID" />
      <TextField source="givenName" />
      <TextField source="familyName" />
      <PhoneField source="telephone" />
      <TextField source="email" />
      <YesNoField source="validatedCandidate" />
      <EditButton />
    </Datagrid>
  </List>
);
