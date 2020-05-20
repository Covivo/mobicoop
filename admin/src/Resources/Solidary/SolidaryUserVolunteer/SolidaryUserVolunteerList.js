import React from 'react';

import {
  List,
  Datagrid,
  TextInput,
  Filter,
  TextField,
  EditButton,
  BooleanInput,
  ReferenceInput,
  SelectInput,
} from 'react-admin';

import { DayField } from './Fields/DayField';
import { AddressField } from './Fields/AddressField';
import { RoleField } from './Fields/RoleField';

const SolidaryUserVolunteerFilter = (props) => (
  <Filter {...props}>
    <TextInput source="givenName" alwaysOn />
    <ReferenceInput alwaysOn label="Post" source="solidary" reference="solidaries">
      <SelectInput optionText="title" />
    </ReferenceInput>
    <BooleanInput
      label="custom.solidary_volunteers.filters.validatedCandidate"
      source="validatedCandidate"
      alwaysOn
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
