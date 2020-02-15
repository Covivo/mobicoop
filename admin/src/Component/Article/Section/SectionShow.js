import React from 'react';
import { 
    Show, 
    ReferenceField, TextField, SelectField, Link,
    Tab, TabbedShowLayout, 
    Datagrid,
    Button, EditButton, DeleteButton,
    ReferenceArrayField
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