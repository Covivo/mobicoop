import React from 'react';
import { 
    Create,
    SimpleForm, 
    required,
    ReferenceInput, SelectInput, NumberInput
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';
import { parse } from "query-string";

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

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
            <SelectInput source="status" label="Statut" choices={statusChoices} defaultValue={0} validate={required()}/>
            <RichTextInput source="text" label="Texte" validate={required()} />
            <NumberInput source="position" label="Position" />
        </SimpleForm>
    </Create>
    );
}