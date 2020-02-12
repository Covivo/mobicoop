import React from 'react';
import { 
    Create, 
    SimpleForm, 
    required,
    TextInput
} from 'react-admin';

export const TerritoryCreate = (props) => (
    <Create { ...props } title="Territoires > ajouter">
        <SimpleForm>
            <TextInput source="name" label="Nom" validate={required()}/>
        </SimpleForm>
    </Create>
);