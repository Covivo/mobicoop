import React from 'react';
import { 
    Create,
    SimpleForm, 
    required,
    TextInput
} from 'react-admin';

export const RelayPointTypeCreate = (props) => (
    <Create { ...props } title="Types de points relais > ajouter">
        <SimpleForm>
            <TextInput source="name" label="Nom" validate={required()}/>
        </SimpleForm>
    </Create>
);