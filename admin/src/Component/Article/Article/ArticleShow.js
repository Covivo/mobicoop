import React from 'react';
import { 
    Show, 
    Tab, TabbedShowLayout, 
    Link, 
    Datagrid,
    Button, ShowButton, EditButton, DeleteButton,
    TextField, ReferenceArrayField, SelectField
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En cours d\'édition' },
    { id: 1, name: 'En ligne' },
];

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