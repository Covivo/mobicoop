import React from 'react';
import { Edit, SimpleForm, DisabledInput, TextInput, email, ArrayInput, SimpleFormIterator, DateInput, SelectInput } from 'react-admin';

const choices = [
    { id: 1, name: 'Femme' },
    { id: 2, name: 'Homme' },
    { id: 3, name: 'Autre' },
];

export const UserEdit = (props) => (
    <Edit {...props}>
        <SimpleForm>
            <DisabledInput source="id" label="ID"/>
            <TextInput source="givenName" label="Prénom"/>
            <TextInput source="familyName" label="Nom"/>
            <SelectInput label="Sexe" source="gender" choices={choices} />
            <TextInput source="email" label="Email" validate={ email() } />
            <DateInput disabled source="createdDate" label="Date de création"/>
        </SimpleForm>
    </Edit>
);