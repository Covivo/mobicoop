import React from 'react';

import { 
    Edit, SimpleForm,
    TextInput,
    DateTimeInput, ReferenceInput, SelectInput, LongTextInput
} from 'react-admin';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;

export const EventEdit = (props) => (
    <Edit { ...props } title="Evénement > afficher">
        <SimpleForm>
            <TextInput source="name" label="Nom" />
            <ReferenceInput source="user" label="Créateur" reference="users">
                <SelectInput optionText={userOptionRenderer} />
            </ReferenceInput>
            <LongTextInput source="description" label="Description" />
            <LongTextInput source="fullDescription" label="Description complète" />
            <DateTimeInput source="fromDate" label="Date de début" />
            <DateTimeInput source="toDate" label="Date de fin" />
            <TextInput source="url" />
        </SimpleForm>
    </Edit>
);