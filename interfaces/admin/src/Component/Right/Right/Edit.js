import React from 'react';
import { Edit, SimpleForm, DisabledInput, TextInput, SelectInput, ReferenceInput } from 'react-admin';

const choices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

export const RightEdit = (props) => (
    <Edit {...props}>
        <SimpleForm>
            <DisabledInput source="id" label="ID"/>
            <SelectInput label="Type" source="type" choices={choices} />
            <TextInput source="name" label="Nom" />
            <ReferenceInput label="Groupe" source="parent" reference="rights" filter={{ type: 2 }}>
                <SelectInput optionText="name" />
            </ReferenceInput>
        </SimpleForm>
    </Edit>
);