import React from 'react';
import { Show, SimpleShowLayout, TextField, ReferenceField, SelectField, ReferenceArrayField, SingleFieldList, ChipField, EditButton } from 'react-admin';

const choices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

export const RoleShow = (props) => (
    <Show { ...props }>
        <SimpleShowLayout>
            <TextField source="id" label="ID"/>
            <TextField source="title" label="Titre"/>
            <TextField source="name" label="Nom"/>
            <ReferenceField label="Groupe" source="parent" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <ReferenceArrayField label="Droits" reference="rights" source="rights">
                <SingleFieldList>
                    <ChipField source="name" />
                </SingleFieldList>
            </ReferenceArrayField>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);