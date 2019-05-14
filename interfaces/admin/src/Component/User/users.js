import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, 
    SimpleShowLayout,
    Datagrid,
    TextInput, DisabledInput, DateInput, SelectInput,
    email,
    TextField, EmailField, DateField, 
    ShowButton, EditButton,
} from 'react-admin';

const genderChoices = [
    { id: 1, name: 'Femme' },
    { id: 2, name: 'Homme' },
    { id: 3, name: 'Autre' },
];

// Create
export const UserCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <TextInput source="givenName" label="Prénom"/>
            <TextInput source="familyName" label="Nom"/>
            <TextInput source="email" label="Email" validate={ email() } />
        </SimpleForm>
    </Create>
);

// Edit
export const UserEdit = (props) => (
    <Edit {...props}>
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <TextInput source="givenName" label="Prénom"/>
            <TextInput source="familyName" label="Nom"/>
            <SelectInput label="Sexe" source="gender" choices={genderChoices} />
            <TextInput source="email" label="Email" validate={ email() } />
            <DateInput disabled source="createdDate" label="Date de création"/>
        </SimpleForm>
    </Edit>
);

// List
export const UserList = (props) => (
    <List {...props} title="Users" perPage={ 30 }>
        <Datagrid>
            <TextField source="originId" label="ID"/>
            <TextField source="givenName" label="Prénom"/>
            <TextField source="familyName" label="Nom"/>
            <EmailField source="email" label="Email" />
            <DateField source="createdDate" label="Date de création"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
export const UserShow = (props) => (
    <Show { ...props }>
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="givenName" label="Prénom"/>
            <TextField source="familyName" label="Nom"/>
            <EmailField source="email" label="Email" />
            <DateField source="createdDate" label="Date de création"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);