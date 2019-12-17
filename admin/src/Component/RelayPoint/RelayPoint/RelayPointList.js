import React from 'react';
import { 
    List,
    Datagrid,
    TextInput, SelectInput,
    TextField, SelectField, ReferenceField, FunctionField,
    Filter,
    EditButton,
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Actif' },
    { id: 2, name: 'Inactif' },
];  

const addressRenderer = address => `${address.displayLabel[0]}`;

const RelayPointFilter = (props) => (
    <Filter {...props}>
        <TextInput source="name" label="Nom" alwaysOn />
        <SelectInput source="status" label="Status" choices={statusChoices} />
    </Filter>
);
const RelayPointPanel = ({ id, record, resource }) => (
    <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
);
export const RelayPointList = (props) => (
    <List {...props} title="Points relais > liste" perPage={ 25 } filters={<RelayPointFilter />} sort={{ field: 'originId', order: 'ASC' }}>
        <Datagrid expand={<RelayPointPanel />} rowClick="show">
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <ReferenceField source="address" label="Adresse" reference="addresses" linkType="">
                <FunctionField render={addressRenderer} />
            </ReferenceField>
            <SelectField source="status" label="Status" choices={statusChoices} sortable={false} />
            <TextField source="description" label="Description"/>
            <EditButton />
        </Datagrid>
    </List>
);