import React from 'react';
import { Show, SimpleShowLayout, TextField, ReferenceField, SelectField, EditButton } from 'react-admin';

const choices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

export const RightShow = (props) => (
    <Show { ...props }>
        <SimpleShowLayout>
            <TextField source="id" label="ID"/>
            <SelectField label="Type" source="type" choices={choices} sortable={false} />
            <TextField source="name" label="Nom"/>
            <ReferenceField label="Groupe" source="parent" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);