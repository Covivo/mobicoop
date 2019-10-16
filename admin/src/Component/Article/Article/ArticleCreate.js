import React from 'react';
import { 
    Create,
    SimpleForm, required, 
    TextInput, SelectInput
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

export const ArticleCreate = (props) => (
    <Create { ...props } title="Articles > ajouter">
        <SimpleForm>
            <TextInput source="title" label="Titre" />
            <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={0} validate={required()}/>
        </SimpleForm>
    </Create>
);