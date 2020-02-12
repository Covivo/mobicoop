import React from 'react';
import { 
    Create,
    SimpleForm, 
    required,
    ReferenceInput, SelectInput, TextInput, NumberInput
} from 'react-admin';
import { parse } from "query-string";

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

export const SectionCreate = (props) => {
    const { article: article_string } = parse(props.location.search);
    const article = article_string ? parseInt(article_string, 10) : '';
    const article_uri = encodeURIComponent(article_string);
    const redirect = article_uri ? `/articles/${article_uri}/show/sections` : 'show';

    return (
    <Create { ...props } title="Articles > ajouter une section">
        <SimpleForm
            defaultValue={{ article }}
            redirect={redirect}
        >
            <ReferenceInput source="article" label="Article" reference="articles" validate={required()}>
                <SelectInput optionText="title" />
            </ReferenceInput>
            <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={0} validate={required()}/>
            <TextInput source="title" label="Titre" />
            <TextInput source="subTitle" label="Sous-titre" />
            <NumberInput source="position" label="Position" />
        </SimpleForm>
    </Create>
    );
}