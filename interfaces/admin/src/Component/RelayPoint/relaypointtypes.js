import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, 
    SimpleShowLayout,
    Datagrid, required,
    TextInput, DisabledInput, 
    TextField, DateField, 
    ShowButton, EditButton,
} from 'react-admin';

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

// List
export const RelayPointTypeList = (props) => (
    <List {...props} title="Types de points relais > liste" perPage={ 30 }>
        <Datagrid>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <DateField source="createdDate" label="Date de création"/>
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
            <DateField source="createdDate" label="Date de création"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);