import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, 
    SimpleShowLayout,
    Datagrid, required,
    TextInput, DisabledInput, 
    TextField, 
    ShowButton, EditButton,
} from 'react-admin';

// Create
export const TerritoryCreate = (props) => (
    <Create { ...props } title="Territoires > ajouter">
        <SimpleForm>
            <TextInput source="name" label="Nom" validate={required()}/>
        </SimpleForm>
    </Create>
);

// Edit
export const TerritoryEdit = (props) => (
    <Edit {...props} title="Territoires > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <TextInput source="name" label="Nom" validate={required()}/>
        </SimpleForm>
    </Edit>
);

// List
export const TerritoryList = (props) => (
    <List {...props} title="Territoires > liste" perPage={ 25 }>
        <Datagrid>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
export const TerritoryShow = (props) => (
    <Show { ...props } title="Territoires > afficher">
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);