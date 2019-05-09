import React from 'react';
import { Create, SimpleForm, TextInput, email, required, ArrayInput, SimpleFormIterator } from 'react-admin';

export const UserCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <TextInput source="givenName" label="Prénom"/>
            <TextInput source="familyName" label="Prénom"/>
            <TextInput source="email" label="Email" validate={ email() } />
            <ArrayInput source="roles" label="Roles">
                <SimpleFormIterator>
                    <TextInput />
                </SimpleFormIterator>
            </ArrayInput>
        </SimpleForm>
    </Create>
);