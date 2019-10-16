import React from 'react';
import { 
    Edit,
    SimpleForm, 
    required,
    TextInput, DisabledInput 
} from 'react-admin';

export const RelayPointTypeEdit = (props) => (
    <Edit {...props} title="Types de points relais > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <TextInput source="name" label="Nom" validate={required()}/>
        </SimpleForm>
    </Edit>
);