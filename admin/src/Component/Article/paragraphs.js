import React from 'react';
import { 
    Create, Edit,
    SimpleForm, 
    required,
    ReferenceInput, SelectInput, NumberInput,
    ReferenceField, TextField
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';
import { parse } from "query-string";

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

// Create
export const ParagraphCreate = (props) => {
    const { section: section_string } = parse(props.location.search);
    const section = section_string ? parseInt(section_string, 10) : '';
    const section_uri = encodeURIComponent(section_string);
    const redirect = section_uri ? `/sections/${section_uri}/show/paragraphs` : 'show';

    return (
    <Create { ...props } title="Articles > ajouter un paragraphe">
        <SimpleForm
            defaultValue={{ section }}
            redirect={redirect}
        >
            <ReferenceInput source="section" label="Section" reference="sections" validate={required()}>
                <SelectInput optionText="title"/>
            </ReferenceInput>
            <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={0} validate={required()}/>
            <RichTextInput source="text" label="Texte" validate={required()} />
            <NumberInput source="position" label="Position" />
        </SimpleForm>
    </Create>
    );
}

// Edit
export const ParagraphEdit = (props) => {
    
    const redirect = `/sections/`;

    return (
    <Edit { ...props } title="Articles > éditer un paragraphe">
        <SimpleForm
            redirect={redirect}
        >
            <ReferenceField source="section" label="Section" reference="sections" linkType="" >
                <TextField source="title"/>
            </ReferenceField>
            <SelectInput source="status" label="Status" choices={statusChoices} />
            <RichTextInput source="text" label="Texte" validate={required()} />
            <NumberInput source="position" label="Position" />
        </SimpleForm>
    </Edit>
    );
}
