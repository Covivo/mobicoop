import React from 'react';
import { 
    Show,
    SimpleShowLayout,
    TextField,
    EditButton
} from 'react-admin';

export const RelayPointTypeShow = (props) => (
    <Show { ...props } title="Types de points relais > afficher">
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);