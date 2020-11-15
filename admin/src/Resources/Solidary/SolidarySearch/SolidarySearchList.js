import React from 'react';
import { useListController } from 'ra-core';

import {
  List,
  Datagrid,
  TextInput,
  TextField,
  Filter,
  AutocompleteInput,
  ReferenceInput,
  BooleanField,
  SelectInput,
} from 'react-admin';

import { solidaryLabelRenderer } from '../../../utils/renderers';
import { JourneyField } from './Field/JourneyField';
import { FrequencyField } from './Field/FrequencyField';
import { DayField } from './Field/DayField';
import { RoleField } from './Field/RoleField';
import { ScheduleDaysField } from './Field/ScheduleDaysField';
import { useSolidary } from '../Solidary/hooks/useSolidary';
import  isAuthorized from '../../../auth/permissions';

import {
  SolidaryUserVolunteerActionDropDown,
  ADDPOTENTIAL_OPTION,
} from '../SolidaryUserVolunteer/SolidaryUserVolunteerActionDropDown';

const SolidarySearchFilter = (props) => {
  return (
    <Filter {...props}>
      <TextInput source="name" label="Nom" alwaysOn />
      {isAuthorized('solidary_volunteer_list') && (
        <SelectInput
          source="type"
          label="Type"
          choices={[
            { id: 'carpool', name: 'Covoiturage' },
            { id: 'transport', name: 'Transport' },
          ]}
          defaultValue="carpool"
        />
      )}
      <SelectInput
        source="way"
        label="Aller ou retour ?"
        choices={[
          { id: 'outward', name: 'Aller' },
          { id: 'return', name: 'Retour' },
        ]}
        defaultValue="outward"
      />
      <ReferenceInput
        alwaysOn
        fullWidth
        label="Demande solidaire"
        source="solidary"
        reference="solidaries"
      >
        <AutocompleteInput allowEmpty optionText={(record) => solidaryLabelRenderer({ record })} />
      </ReferenceInput>
    </Filter>
  );
};

const ActionsDropDown = ({ record, onRefresh, ...props }) => {
  // @TODO: Will work when authorId is available on the API
  // Moreover, shouldn't we retrieve the corresponding solution matchin instead of checking user ?
  const isAlreadySelected =
    props.solidary &&
    Array.isArray(props.solidary.solutions) &&
    props.solidary.solutions.find((s) => s.UserId === record.solidaryResultCarpool.authorId);

  return (
    <SolidaryUserVolunteerActionDropDown
      {...props}
      omittedOptions={[isAlreadySelected && ADDPOTENTIAL_OPTION].filter((x) => x)}
      userId={record.solidaryResultCarpool.authorId}
      onActionFinished={onRefresh}
      type="carpool"
    />
  );
};

const CarpoolDatagrid = ({ solidary, onRefresh, ...props }) => (
  <Datagrid {...props}>
    <TextField
      source="solidaryResultCarpool.author"
      label="resources.solidary_searches.fields.author"
    />
    <JourneyField
      source="solidaryResultCarpool"
      label="resources.solidary_searches.fields.journey"
    />
    <ScheduleDaysField
      source="solidaryResultCarpool.schedule"
      label="resources.solidary_searches.fields.schedule"
    />
    <FrequencyField
      source="solidaryResultCarpool.frequency"
      label="resources.solidary_searches.fields.type"
    />
    <RoleField
      source="solidaryResultCarpool.role"
      label="resources.solidary_searches.fields.role"
    />
    <BooleanField
      source="solidaryResultCarpool.solidaryExlusive"
      label="resources.solidary_searches.fields.exclusive"
    />
    <ActionsDropDown label="Action" solidary={solidary} onRefresh={onRefresh} />
  </Datagrid>
);

const TransportDatagrid = (
  <Datagrid>
    <TextField
      source="solidaryResultTransport.home"
      label="resources.solidary_searches.fields.origin"
    />
    <TextField
      source="solidaryResultTransport.volunteer"
      label="resources.solidary_searches.fields.volunteer"
    />
    <DayField label="custom.days.mon" source="solidaryResultTransport.schedule.mon" />
    <DayField label="custom.days.tue" source="solidaryResultTransport.schedule.tue" />
    <DayField label="custom.days.wed" source="solidaryResultTransport.schedule.wed" />
    <DayField label="custom.days.thu" source="solidaryResultTransport.schedule.thu" />
    <DayField label="custom.days.fri" source="solidaryResultTransport.schedule.fri" />
    <DayField label="custom.days.sat" source="solidaryResultTransport.schedule.sat" />
    <DayField label="custom.days.sun" source="solidaryResultTransport.schedule.sun" />
  </Datagrid>
);

export const SolidarySearchListGuesser = (props) => {
  const { solidary, refresh } = useSolidary(props.filterValues.solidary);

  // Resolve datagrid fields from return data
  // if loading => display null because fields should not match previous data
  // if solidaryResultCarpool is not null => it's a carpool list
  // if solidaryResultTransport is not null => it's a transport list
  const dynamicDatagrid = props.loading ? null : props.ids.length > 0 &&
    props.data[props.ids[0]].solidaryResultCarpool !== null ? (
    <CarpoolDatagrid solidary={solidary} onRefresh={refresh} />
  ) : (
    TransportDatagrid
  );

  return (
    <List
      {...props}
      title="Covoiturages"
      perPage={25}
      filters={<SolidarySearchFilter />}
      exporter={isAuthorized('export') ? undefined : false}
      filterDefaultValues={{ way: 'outward', type: 'carpool' }}
    >
      {dynamicDatagrid}
    </List>
  );
};

export const SolidarySearchList = (props) => (
  <SolidarySearchListGuesser {...props} {...useListController(props)} />
);
