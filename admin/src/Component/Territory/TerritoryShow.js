import React from 'react';
import { 
    Show,
    SimpleShowLayout,
    TextField, 
    EditButton
} from 'react-admin';

export const TerritoryShow = (props) => (
    <Show { ...props } title="Territoires > afficher">
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);