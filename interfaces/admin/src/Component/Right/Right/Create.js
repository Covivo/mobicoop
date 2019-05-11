import React from 'react';
import { Create, SimpleForm, TextInput, SelectInput, ReferenceInput } from 'react-admin';

const choices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

export const RightCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <SelectInput source="type" label="Type" choices={choices} />
            <TextInput source="name" label="Nom" />
            <ReferenceInput label="Groupe" source="parent" reference="rights" filter={{ type: 2 }}>
                <SelectInput optionText="name" />
            </ReferenceInput>
        </SimpleForm>
    </Create>
);