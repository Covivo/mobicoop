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
  useListController,
} from 'react-admin';

import { DayField } from './Fields/DayField';
import { AddressField } from './Fields/AddressField';
import { RoleField } from './Fields/RoleField';
import { solidaryLabelRenderer } from '../../../utils/renderers';
import { useSolidary } from '../Solidary/hooks/useSolidary';
import { defaultExporterFunctionSuperAdmin } from '../../../utils/utils';

import {
  SolidaryUserVolunteerActionDropDown,
  ADDPOTENTIAL_OPTION,
} from './SolidaryUserVolunteerActionDropDown';

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
      <SelectInput optionText={(record) => solidaryLabelRenderer({ record })} />
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

const ActionsDropDown = ({ record, onRefresh, ...props }) => {
  // @TODO: Shouldn't we retrieve the corresponding solution matchin instead of checking user ?
  const isAlreadySelected = props.solidary.solutions.find((s) => s.UserId === record.user.originId);

  return (
    <SolidaryUserVolunteerActionDropDown
      {...props}
      omittedOptions={[isAlreadySelected && ADDPOTENTIAL_OPTION].filter((x) => x)}
      userId={record.user.originId}
      onActionFinished={onRefresh}
    />
  );
};

export const SolidaryUserVolunteerListGuesser = (props) => {
  const { solidary, refresh } = useSolidary(props.filterValues.solidary);

  return (
    <List
      {...props}
      bulkActionButtons={false}
      title="Transporteurs bénévoles > liste"
      perPage={25}
      filters={<SolidaryUserVolunteerFilter />}
      filterDefaultValues={{ validatedCandidate: false }}
      exporter={defaultExporterFunctionSuperAdmin()}
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
        {solidary ? <ActionsDropDown solidary={solidary} onRefresh={refresh} /> : <EditButton />}
      </Datagrid>
    </List>
  );
};

export const SolidaryUserVolunteerList = (props) => (
  <SolidaryUserVolunteerListGuesser {...props} {...useListController(props)} />
);
