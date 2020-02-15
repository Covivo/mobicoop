import React from 'react';

import { 
    Edit, SimpleForm,
    TextInput,
    DateTimeInput, ReferenceInput, SelectInput,
} from 'react-admin';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;

const required = (message = 'ra.validation.required') =>
    (value, allValues, props) => value ? undefined : props.translate(message);

export const EventEdit = (props) => (
    <Edit { ...props } title="Evénement > afficher">
        <SimpleForm>
            <TextInput source="name" label="Nom" />
            <ReferenceInput source="user" label="Créateur" reference="users">
                <SelectInput optionText={userOptionRenderer} />
            </ReferenceInput>
            <TextInput multiline source="description" label="Description" validate={[required()]} />
            <TextInput multiline source="fullDescription" label="Description complète" validate={[required()]} />
            <DateTimeInput source="fromDate" label="Date de début" validate={[required()]} />
            <DateTimeInput source="toDate" label="Date de fin" validate={[required()]} />
            <TextInput source="url" type="url" />
        </SimpleForm>
    </Edit>
);