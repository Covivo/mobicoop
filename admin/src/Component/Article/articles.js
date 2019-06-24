import React from 'react';
import { 
    Create, Edit, List, Show, 
    Tab, TabbedShowLayout, 
    Link, 
    Datagrid,
    SimpleForm, required, 
    DisabledInput, TextInput, SelectInput,
    Button, ShowButton, EditButton, DeleteButton,
    TextField, ReferenceArrayField, ReferenceManyField, ChipField, SingleFieldList, SelectField
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

// List
export const ArticleList = (props) => (
    <List {...props} title="Articles > liste" perPage={ 25 } sort={{ field: 'originId', order: 'ASC' }}>
        <Datagrid>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="title" label="Titre"/>
            <SelectField source="status" label="Status" choices={statusChoices} />
            <ReferenceManyField label="Sections" reference="sections" target="article" sortable={false}>
                <SingleFieldList linkType="show">
                    <ChipField source="title" />
                </SingleFieldList>
            </ReferenceManyField>
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
                <TextField source="originId" label="ID"/>
                <TextField source="title" label="Titre"/>
                <SelectField source="status" label="Status" choices={statusChoices} />
                <EditButton />
            </Tab>
            <Tab label="Sections" path="sections">
                <ReferenceArrayField source="sections" reference="sections" addLabel={false}>
                    <Datagrid>
                        <TextField source="title" label="Titre" />
                        <TextField source="subtitle" label="Sous-titre" />
                        <TextField source="position" label="Position" />
                        <SelectField source="status" label="Status" choices={statusChoices} />
                        <ShowButton />
                        <EditButton />
                        <DeleteButton />
                    </Datagrid>
                </ReferenceArrayField>
                <AddSectionButton />
            </Tab>
        </TabbedShowLayout>
    </Show>
);

// Create
export const ArticleCreate = (props) => (
    <Create { ...props } title="Articles > ajouter">
        <SimpleForm>
            <TextInput source="title" label="Titre" />
            <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={0} validate={required()}/>
        </SimpleForm>
    </Create>
);

// Edit
export const ArticleEdit = (props) => (
    <Edit {...props } title="Articles > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <TextInput source="title" label="Titre" />
            <SelectInput source="status" label="Status" choices={statusChoices} validate={required()}/>
        </SimpleForm>
    </Edit>
);