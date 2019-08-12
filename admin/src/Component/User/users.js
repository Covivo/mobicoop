import React from 'react';
//import bcrypt from 'bcryptjs';

import { 
    Create, Edit, List, Show,
    TabbedForm, FormTab,
    TabbedShowLayout, Tab,
    Datagrid,
    TextInput, DisabledInput, SelectInput, DateInput, ReferenceInput, BooleanInput,
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

// List
const UserFilter = (props) => (
    <Filter {...props}>
        <TextInput source="givenName" label="Prénom" />
        <TextInput source="familyName" label="Nom" alwaysOn />
        <TextInput source="email" label="Email" alwaysOn />
        <BooleanInput source="solidary" label="Solidaire" allowEmpty={false} defaultValue={true} />
        <ReferenceInput 
            source="homeAddressTerritory" 
            label="Territoire" 
            reference="territories" 
            allowEmpty={false} 
            resettable>
            <SelectInput optionText="name" optionValue="id"/>
        </ReferenceInput>
    </Filter>
);
export const UserList = (props) => (
    <List {...props} title="Utilisateurs > liste" perPage={ 25 } filters={<UserFilter />} sort={{ field: 'id', order: 'ASC' }}>
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
        <TabbedShowLayout>
            <Tab label="Identité">
                <TextField source="originId" label="ID"/>
                <TextField source="givenName" label="Prénom"/>
                <TextField source="familyName" label="Nom"/>
                <EmailField source="email" label="Email" />
                <DateField source="createdDate" label="Date de création"/>
                <EditButton />
            </Tab>
            <Tab label="Préférences">

            </Tab>
            <Tab label="Adresses">

            </Tab>
        </TabbedShowLayout>
    </Show>
);

// Create
export const UserCreate = (props) => (
    <Create { ...props } title="Utilisateurs > ajouter">
        <TabbedForm>
            <FormTab label="Identité">
                <TextInput source="givenName" label="Prénom"/>
                <TextInput source="familyName" label="Nom"/>
                <SelectInput source="gender" label="Sexe" choices={genderChoices} />
                <TextInput source="email" label="Email" validate={ email() } />
                <DateInput source="birthDate" label="Date de naissance" />
                <TextInput source="telephone" label="Téléphone"/>
            </FormTab>
            <FormTab label="Sécurité">
                <TextInput source="password" label="Mot de passe" type="password"/>
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
                <SelectInput source="gender" label="Sexe" choices={genderChoices} />
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