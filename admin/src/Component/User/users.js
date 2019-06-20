import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, TabbedForm, FormTab,
    SimpleShowLayout,
    Datagrid,
    TextInput, DisabledInput, SelectInput, DateInput,
    email,
    TextField, EmailField, DateField, 
    ShowButton, EditButton,
    Filter
} from 'react-admin';

const genderChoices = [
    { id: 1, name: 'Femme' },
    { id: 2, name: 'Homme' },
    { id: 3, name: 'Autre' },
];

// Create
export const UserCreate = (props) => (
    <Create { ...props } title="Utilisateurs > ajouter">
        <TabbedForm>
            <FormTab label="Identité">
                <TextInput source="givenName" label="Prénom"/>
                <TextInput source="familyName" label="Nom"/>
                <SelectInput label="Sexe" source="gender" choices={genderChoices} />
                <TextInput source="email" label="Email" validate={ email() } />
                <DateInput source="birthDate" label="Date de naissance" />
                <TextInput source="telephone" label="Téléphone"/>
            </FormTab>
            <FormTab label="Sécurité">
                <TextInput source="password" label="Mot de passe"  type="password" />
            </FormTab>
            <FormTab label="Préférences">

            </FormTab>
            <FormTab label="Adresses">

            </FormTab>
        </TabbedForm>
</Create>
);

// Edit
export const UserEdit = (props) => (
    <Edit {...props} title="Utilisateurs > éditer">
        <TabbedForm>
            <FormTab label="Identité">
                <DisabledInput source="originId" label="ID"/>
                <TextInput source="givenName" label="Prénom"/>
                <TextInput source="familyName" label="Nom"/>
                <SelectInput label="Sexe" source="gender" choices={genderChoices} />
                <TextInput source="email" label="Email" validate={ email() } />
                <DateInput source="birthDate" label="Date de naissance" />
                <TextInput source="telephone" label="Téléphone"/>
            </FormTab>
            <FormTab label="Préférences">

            </FormTab>
            <FormTab label="Adresses">

            </FormTab>
        </TabbedForm>
    </Edit>
);

// List
const UserFilter = (props) => (
    <Filter {...props}>
        <TextInput source="givenName" label="Prénom" />
        <TextInput source="familyName" label="Nom" alwaysOn />
        <TextInput source="email" label="Email" alwaysOn />
    </Filter>
);

export const UserList = (props) => (
    <List {...props} title="Utilisateurs > liste" perPage={ 25 } filters={<UserFilter />}>
        <Datagrid>
            <TextField source="originId" label="ID" sortBy="id"/>
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
    <Show { ...props } title="Utilisateurs > afficher">
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