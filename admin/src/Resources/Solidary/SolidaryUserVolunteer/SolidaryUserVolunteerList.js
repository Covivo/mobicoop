import React from 'react';

import {
  List,
  Datagrid,
  TextInput,
  Filter,
  TextField,
  EditButton,
  ReferenceInput,
  SelectInput,
  NullableBooleanInput,
} from 'react-admin';

import { DayField } from './Fields/DayField';
import { AddressField } from './Fields/AddressField';
import { RoleField } from './Fields/RoleField';

const SolidaryUserVolunteerFilter = (props) => (
  <Filter {...props}>
    <TextInput source="givenName" alwaysOn />
    <TextInput source="familyName" alwaysOn />
    <ReferenceInput
      alwaysOn
      label="custom.solidary_volunteers.input.solidary"
      source="solidary"
      reference="solidaries"
    >
      <SelectInput optionText="id" />
    </ReferenceInput>
    <NullableBooleanInput
      alwaysOn
      displayNull
      label="custom.solidary_volunteers.input.validatedCandidate"
      source="validatedCandidate"
      choices={[{ id: false, name: 'Candidats' }]}
    />
  </Filter>
);

export const SolidaryUserVolunteerList = (props) => (
  <List
    {...props}
    title="Transporteurs Bénévoles > liste"
    perPage={25}
    filters={<SolidaryUserVolunteerFilter />}
    filterDefaultValues={{ validatedCandidate: false }}
  >
    <Datagrid>
      <TextField source="givenName" />
      <TextField source="familyName" />
      <RoleField
        source="validatedCandidate"
        fillRoleLabel="Bénévole"
        unfulfillRoleLabel="Candidat Bénévole"
      />
      <AddressField source="homeAddress" />
      <DayField source="Mon" />
      <DayField source="Tue" />
      <DayField source="Wed" />
      <DayField source="Thu" />
      <DayField source="Fri" />
      <DayField source="Sat" />
      <DayField source="Sun" />
      <EditButton />
    </Datagrid>
  </List>
);
