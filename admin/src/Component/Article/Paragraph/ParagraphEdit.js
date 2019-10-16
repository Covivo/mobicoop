import React from 'react';
import { 
    Edit,
    SimpleForm, 
    required,
    SelectInput, NumberInput,
    ReferenceField, TextField
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

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
            <SelectInput source="status" label="Statut" choices={statusChoices} />
            <RichTextInput source="text" label="Texte" validate={required()} />
            <NumberInput source="position" label="Position" />
        </SimpleForm>
    </Edit>
    );
}
