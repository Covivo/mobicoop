import React from 'react';
import { 
    Create, Edit, List, Show, 
    Tab, TabbedShowLayout, 
    Link, 
    Datagrid,
    SimpleForm, required, 
    DisabledInput, TextInput, SelectInput,
    Button, ShowButton, EditButton, DeleteButton,
    TextField, ReferenceArrayField, ReferenceField
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

// Create
export const ArticleCreate = (props) => (
    <Create { ...props } title="Articles > ajouter">
        <SimpleForm>
            <TextInput source="title" label="Titre" />
            <SelectInput label="Status" source="status" choices={statusChoices} defaultValue={0} validate={required()}/>
        </SimpleForm>
    </Create>
);

// Edit
export const ArticleEdit = (props) => (
    <Edit {...props } title="Articles > éditer">
        <SimpleForm>
            <DisabledInput source="id" label="ID"/>
            <TextInput source="title" label="Titre" />
            <SelectInput label="Status" source="status" choices={statusChoices} validate={required()}/>
        </SimpleForm>
    </Edit>
);

// List
export const ArticleList = (props) => (
    <List {...props} title="Articles > liste" perPage={ 30 }>
        <Datagrid>
            <TextField source="id" label="ID"/>
            <TextField source="title" label="Titre"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
const AddSectionButton = ({ record }) => (
    <Button
        component={Link}
        to={{
            pathname: `/sections/create`,
            search: `?article=${record.id}`
        }}
        label="Ajouter une section"
    >
    </Button>
);
export const ArticleShow = (props) => (
    <Show { ...props } title="Articles > afficher">
        <TabbedShowLayout>
            <Tab label="Détails">
                <TextField source="id" label="ID"/>
                <TextField source="title" label="Titre"/>
                <EditButton />
            </Tab>
            <Tab label="Sections" path="sections">
                <ReferenceArrayField reference="sections" source="sections" addLabel={false}>
                    <Datagrid>
                        <ReferenceField label="Title" source="section" reference="sections" linkType="">
                            <TextField source="title" />
                        </ReferenceField>
                        <EditButton />
                        <DeleteButton />
                    </Datagrid>
                </ReferenceArrayField>
                <AddSectionButton />
            </Tab>
        </TabbedShowLayout>
    </Show>
);