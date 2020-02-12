import React from 'react';
import { 
    Edit,
    SimpleForm, required, 
    TextInput, SelectInput
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

export const ArticleEdit = (props) => (
    <Edit {...props } title="Articles > éditer">
        <SimpleForm>
            <TextInput disabled source="originId" label="ID"/>
            <TextInput source="title" label="Titre" />
            <SelectInput source="status" label="Status" choices={statusChoices} validate={required()}/>
        </SimpleForm>
    </Edit>
);