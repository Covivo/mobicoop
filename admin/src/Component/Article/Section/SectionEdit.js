import React from 'react';
import { 
    Edit,
    SimpleForm, 
    SelectInput, TextInput, NumberInput,
    ReferenceField, TextField, SelectField,
    Link, 
    Datagrid,
    Button, EditButton,
    ReferenceManyField
} from 'react-admin';
import { RichTextField } from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

const AddParagraphButton = ({ record }) => (
    <Button
        component={Link}
        to={{
            pathname: `/paragraphs/create`,
            search: `?section=${record.id}`
        }}
        label="Ajouter un paragraphe"
    >
    </Button>
);
export const SectionEdit = (props) => {
    
    const redirect = `/articles/`;

    return (
    <Edit { ...props } title="Articles > éditer une section">
        <SimpleForm
            redirect={redirect}
        >
            <ReferenceField source="article" label="Article" reference="articles" linkType="show" >
                <TextField source="title"/>
            </ReferenceField>
            <SelectInput source="status" label="Status" choices={statusChoices} />
            <TextInput source="title" label="Titre" />
            <TextInput source="subTitle" label="Sous-titre" />
            <NumberInput source="position" label="Position" />
            <ReferenceManyField label="Paragraphes" reference="paragraphs" target="section">
                <Datagrid>
                    <RichTextField source="text" label="Texte" />
                    <SelectField source="status" label="Status" choices={statusChoices} />
                    <EditButton />
                </Datagrid>
            </ReferenceManyField>
            <AddParagraphButton />
        </SimpleForm>
    </Edit>
    );
}