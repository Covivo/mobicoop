import React from 'react';
import { Create, SimpleForm, TextInput, email } from 'react-admin';

export const UserCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <TextInput source="givenName" label="Prénom"/>
            <TextInput source="familyName" label="Nom"/>
            <TextInput source="email" label="Email" validate={ email() } />
        </SimpleForm>
    </Create>
);