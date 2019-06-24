import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, 
    SimpleShowLayout,
    Datagrid, required,
    TextInput, DisabledInput, 
    TextField, Filter,
    ShowButton, EditButton,
} from 'react-admin';

// List
const RelayPointTypeFilter = (props) => (
    <Filter {...props}>
        <TextInput source="name" label="Nom" alwaysOn />
    </Filter>
);
export const RelayPointTypeList = (props) => (
    <List {...props} title="Types de points relais > liste" perPage={ 25 } filters={<RelayPointTypeFilter />} sort={{ field: 'originId', order: 'ASC' }}>
        <Datagrid>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
export const RelayPointTypeShow = (props) => (
    <Show { ...props } title="Types de points relais > afficher">
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);

// Create
export const RelayPointTypeCreate = (props) => (
    <Create { ...props } title="Types de points relais > ajouter">
        <SimpleForm>
            <TextInput source="name" label="Nom" validate={required()}/>
        </SimpleForm>
    </Create>
);

// Edit
export const RelayPointTypeEdit = (props) => (
    <Edit {...props} title="Types de points relais > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <TextInput source="name" label="Nom" validate={required()}/>
        </SimpleForm>
    </Edit>
);