import React from 'react';

import {
  List,
  Datagrid,
  TextInput,
  SelectInput,
  ReferenceInput,
  AutocompleteInput,
  TextField,
  SelectField,
  ReferenceField,
  FunctionField,
  Filter,
  EditButton,
  useTranslate,
} from 'react-admin';

import isAuthorized from '../../../auth/permissions';

const RelayPointList = (props) => {

  const translate = useTranslate();
  const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Actif' },
    { id: 2, name: 'Inactif' },
  ];

  const addressRenderer = (address) => `${address.displayLabel[0]}`;

  const RelayPointFilter = (props) => (
    <Filter {...props}>
      <TextInput source="name" label="Nom" alwaysOn />
      <SelectInput source="status" label="Status" choices={statusChoices} />
      <ReferenceInput
          source="territory"
          label={translate('custom.label.relayPoint.territory')}
          reference="territories"
          allowEmpty={false}
          resettable
          perPage={20}
          filterToQuery={(searchText) => ({ name: searchText })}
        >
           <AutocompleteInput optionText="name" optionValue="id" />
      </ReferenceInput>
    </Filter>
  );
  const RelayPointPanel = ({ record }) => (
    // eslint-disable-next-line react/no-danger
    <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
  );
  return (
    <List
      {...props}
      title="Points relais > liste"
      perPage={25}
      filters={<RelayPointFilter />}
      sort={{ field: 'originId', order: 'ASC' }}
      exporter={isAuthorized('export') ? undefined : false}
    >
      <Datagrid expand={<RelayPointPanel />} rowClick="show">
        <TextField source="originId" label="ID" sortBy="id" />
        <TextField source="name" label="Nom" />
        <ReferenceField source="address.id" label="Adresse" reference="addresses" linkType="">
          <FunctionField render={addressRenderer} />
        </ReferenceField>
        <SelectField source="status" label="Status" choices={statusChoices} sortable={false} />
        <TextField source="description" label="Description" />
        <EditButton />
      </Datagrid>
    </List> 
  );
};

export default RelayPointList;
