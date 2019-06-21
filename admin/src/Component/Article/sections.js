import React from 'react';
import { 
    Create, Edit, Show, 
    SimpleForm, 
    required,
    ReferenceInput, SelectInput, TextInput, NumberInput,
    ReferenceField, TextField, SelectField,
    Tab, TabbedShowLayout, 
    Link, 
    Datagrid,
    Button, EditButton, DeleteButton,
    ReferenceArrayField, ReferenceManyField
} from 'react-admin';
import { parse } from "query-string";
import { RichTextField } from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

// Show
export const SectionShow = (props) => (
    <Show { ...props } title="Articles > afficher une section">
        <TabbedShowLayout>
            <Tab label="Détails">
                <TextField source="originId" label="ID"/>
                <ReferenceField source="article" label="Article" reference="articles" linkType="show" >
                    <TextField source="title"/>
                </ReferenceField>
                <SelectField source="status" label="Status" choices={statusChoices} />
                <TextField source="title" label="Titre" />
                <TextField source="subtitle" label="Sous-titre" />
                <TextField source="position" label="Position" />
                <EditButton />
            </Tab>
            <Tab label="Paragraphes" path="paragraphs">
                <ReferenceArrayField source="paragraphs" reference="paragraphs" addLabel={false}>
                    <Datagrid>
                        <RichTextField source="text" label="Texte" />
                        <TextField source="position" label="Position" />
                        <EditButton />
                        <DeleteButton />
                    </Datagrid>
                </ReferenceArrayField>
                <AddParagraphButton />
            </Tab>
        </TabbedShowLayout>
    </Show>
);

// Create
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

// Edit
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