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
import { CreateSolidarySolutionButton } from './CreateSolidarySolutionButton';
import { JourneyField } from './Field/JourneyField';
import { FrequencyField } from './Field/FrequencyField';
import { DayField } from './Field/DayField';
import { RoleField } from './Field/RoleField';
import { ScheduleDaysField } from './Field/ScheduleDaysField';

const SolidarySearchFilter = (props) => (
  <Filter {...props}>
    <TextInput source="name" label="Nom" alwaysOn />
    <SelectInput
      source="type"
      label="Type"
      choices={[
        { id: 'carpool', name: 'Covoiturage' },
        { id: 'transport', name: 'Transport' },
      ]}
      defaultValue="carpool"
    />
    <SelectInput
      source="way"
      label="Way"
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

const CarpoolDatagrid = (
  <Datagrid>
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
    <CreateSolidarySolutionButton source="solidaryMatching.id" label="Action" />
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
  // Resolve datagrid fields from return data
  // if loading => display null because fields should not match previous data
  // if solidaryResultCarpool is not null => it's a carpool list
  // if solidaryResultTransport is not null => it's a transport list
  const dynamicDatagrid = props.loading
    ? null
    : props.ids.length > 0 && props.data[props.ids[0]].solidaryResultCarpool !== null
    ? CarpoolDatagrid
    : TransportDatagrid;

  return (
    <List
      {...props}
      title="Covoiturages"
      perPage={25}
      filters={<SolidarySearchFilter />}
      filterDefaultValues={{ way: 'outward', type: 'carpool' }}
    >
      {dynamicDatagrid}
    </List>
  );
};

export const SolidarySearchList = (props) => (
  <SolidarySearchListGuesser {...props} {...useListController(props)} />
);
