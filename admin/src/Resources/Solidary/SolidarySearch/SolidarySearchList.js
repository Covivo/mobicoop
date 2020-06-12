import React from 'react';
import get from 'lodash.get';
import { useListController } from 'ra-core';

import {
  List,
  Datagrid,
  Mutation,
  Button,
  TextInput,
  TextField,
  Filter,
  useTranslate,
  AutocompleteInput,
  ReferenceInput,
  BooleanField,
  SelectInput,
} from 'react-admin';

import { solidarySearchFrequencyLabels } from '../../../constants/solidarySearchFrequency';
import { carpoolRoleLabels } from '../../../constants/solidarySearchRole';

const CreateSolidarySolutionButton = ({ record, source }) => {
  const solidaryMatching = get(record, source);

  return (
    <Mutation
      type="create"
      resource="solidary_solutions"
      payload={{ data: { solidaryMatching: solidaryMatching } }}
      options={{
        onSuccess: {
          notification: { body: 'Trajet ajouté à la demande !' },
        },
        onFailure: {
          notification: {
            body: 'Erreur : le trajet ne peut pas être ajouté à la demande.',
            level: 'warning',
          },
        },
      }}
    >
      {(approve, { loading }) => (
        <Button label="Sélectionner" onClick={approve} disabled={loading} />
      )}
    </Mutation>
  );
};

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
      <AutocompleteInput
        allowEmpty
        optionText={(record) => `${record.originId} - ${record.displayLabel}`}
      />
    </ReferenceInput>
  </Filter>
);

const JourneyField = ({ record, source }) => {
  const journey = get(record, source);
  return `${journey.origin} -> ${journey.destination}`;
};

const ScheduleDaysField = ({ record, source }) => {
  const translate = useTranslate();
  const schedule = get(record, source);

  return (
    <span>
      {Object.keys(schedule)
        .map((day) => {
          return translate(`custom.days.${day}`);
        })
        .join(' ')}
    </span>
  );
};

export const DayField = ({ record, source }) => {
  const morning = get(record, source).m;
  const afternoon = get(record, source).a;
  const evening = get(record, source).e;

  const display = [morning ? 'Mat.' : '-', afternoon ? 'Ap.' : '-', evening ? 'Soir' : '-'].join(
    '<br/>/'
  );

  // eslint-disable-next-line react/no-danger
  return <div dangerouslySetInnerHTML={{ __html: display }} />;
};

const FrequencyField = ({ record, source }) => {
  const translate = useTranslate();
  const frequency = get(record, source);

  return translate(solidarySearchFrequencyLabels[frequency]) || '-';
};

const RoleField = ({ record, source }) => {
  const translate = useTranslate();
  const role = get(record, source);

  return translate(carpoolRoleLabels[role]);
};

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
