import React from 'react';
import { 
    Edit,
    SimpleForm, 
    required,
    TextInput, DisabledInput
} from 'react-admin';

export const TerritoryEdit = (props) => (
    <Edit {...props} title="Territoires > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <TextInput source="name" label="Nom" validate={required()}/>
        </SimpleForm>
    </Edit>
);