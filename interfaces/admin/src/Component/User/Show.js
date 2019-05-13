import React from 'react';
import { Show, SimpleShowLayout, TextField, DateField, EmailField, EditButton } from 'react-admin';

export const UserShow = (props) => (
    <Show { ...props }>
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="givenName" label="Prénom"/>
            <TextField source="familyName" label="Nom"/>
            <EmailField source="email" label="Email" />
            <DateField source="createdDate" label="Date de création"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);