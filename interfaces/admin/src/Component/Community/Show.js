import React from 'react';
import { Show, SimpleShowLayout, TextField, DateField, EmailField, EditButton, RichTextField } from 'react-admin';

export const CommunityShow = (props) => (
    <Show { ...props }>
        <SimpleShowLayout>
            <TextField source="id" label="ID"/>
            <TextField source="name" label="Nom"/>
            <TextField source="description" label="Description"/>
            <RichTextField source="fullDescription" label="Description complète"/>
            <DateField source="createdDate" label="Date de création"/>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);