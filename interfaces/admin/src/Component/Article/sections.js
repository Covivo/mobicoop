import React from 'react';
import { 
    Create, Edit,
    SimpleForm, 
    required,
    ReferenceInput, SelectInput, TextInput, NumberInput,
    ReferenceField, TextField
} from 'react-admin';
import { parse } from "query-string";

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

// Create
export const SectionCreate = (props) => {
    const { article: article_string } = parse(props.location.search);
    const article = article_string ? parseInt(article_string, 10) : '';
    const article_uri = encodeURIComponent(article_string);
    const redirect = article_uri ? `/articles/${article_uri}/show/sections` : 'show';

    return (
    <Create { ...props } title="Articles > sections > ajouter une section">
        <SimpleForm
            defaultValue={{ article }}
            redirect={redirect}
        >
            <ReferenceInput label="Article" source="article" reference="articles" validate={required()}>
                <SelectInput optionText="title" />
            </ReferenceInput>
            <SelectInput label="Status" source="status" choices={statusChoices} defaultValue={0} validate={required()}/>
            <TextInput source="title" label="Titre" />
            <TextInput source="subTitle" label="Sous-titre" />
            <NumberInput source="position" label="Position" />
        </SimpleForm>
    </Create>
    );
}

// Edit
export const SectionEdit = (props) => {
    
    const redirect = `/articles/`;

    return (
    <Edit { ...props } title="Articles > sections > éditer une section">
        <SimpleForm
            redirect={redirect}
        >
            <ReferenceField label="Article" source="article" reference="articles" linkType="" >
                <TextField source="title"/>
            </ReferenceField>
            <SelectInput label="Status" source="status" choices={statusChoices} />
            <TextInput source="title" label="Titre" />
            <TextInput source="subTitle" label="Sous-titre" />
            <NumberInput source="position" label="Position" />
        </SimpleForm>
    </Edit>
    );
}
