import React from 'react';
import { Show, SimpleShowLayout, TextField, ReferenceField, ReferenceArrayField, SingleFieldList, ChipField, EditButton } from 'react-admin';

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