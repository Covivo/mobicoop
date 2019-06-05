import React from 'react';
import { 
    Create, Edit, List, Show, 
    Tab, TabbedShowLayout, 
    Link, 
    Datagrid,
    SimpleForm, required,
    DisabledInput, TextInput, DateInput, BooleanInput, ReferenceInput, SelectInput,
    Button, ShowButton, EditButton, DeleteButton,
    BooleanField, TextField, DateField, RichTextField, SelectField, ReferenceArrayField, ReferenceField
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const userId = parseInt(`${localStorage.getItem('id')}`);
const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Accepté' },
    { id: 2, name: 'Refusé' },
];

// Create
export const CommunityCreate = (props) => (
    <Create { ...props } title="Communautés > ajouter">
        <SimpleForm>
            <ReferenceInput label="Créateur" source="user" reference="users" defaultValue={userId}>
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <TextInput source="name" label="Nom" validate={required()}/>
            <BooleanInput source="private" label="Privée" />
            <TextInput source="description" label="Description" validate={required()}/>
            <RichTextInput source="fullDescription" label="Description complète" validate={required()}/>
        </SimpleForm>
    </Create>
);

// Edit
export const CommunityEdit = (props) => (
    <Edit {...props } title="Communautés > éditer">
        <SimpleForm>
            <DisabledInput source="id" label="ID"/>
            <ReferenceInput label="Créateur" source="user" reference="users">
                <SelectInput optionText={userOptionRenderer} />
            </ReferenceInput>
            <TextInput source="name" label="Nom" validate={required()}/>
            <BooleanInput source="private" label="Privée" />
            <TextInput source="description" label="Description" validate={required()}/>
            <RichTextInput source="fullDescription" label="Description complète" validate={required()} />
            <DateInput disabled source="createdDate" label="Date de création"/>
        </SimpleForm>
    </Edit>
);

// List
export const CommunityList = (props) => (
    <List {...props} title="Communautés > liste" perPage={ 30 }>
        <Datagrid>
            <TextField source="id" label="ID"/>
            <TextField source="name" label="Nom"/>
            <BooleanField source="private" label="Privée" />
            <TextField source="description" label="Description"/>
            <DateField source="createdDate" label="Date de création"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
const AddNewMemberButton = ({ record }) => (
    <Button
        component={Link}
        to={{
            pathname: `/community_users/create`,
            search: `?community=${record.id}`
        }}
        label="Ajouter un membre"
    >
    </Button>
);

export const CommunityShow = (props) => (
    <Show { ...props } title="Communautés > afficher">
        <TabbedShowLayout>
            <Tab label="Détails">
                <TextField source="id" label="ID"/>
                <TextField source="name" label="Nom"/>
                <TextField source="description" label="Description"/>
                <RichTextField source="fullDescription" label="Description complète"/>
                <DateField source="createdDate" label="Date de création"/>
                <EditButton />
            </Tab>
            <Tab label="Membres" path="members">
                <ReferenceArrayField reference="community_users" source="communityUsers" addLabel={false}>
                    <Datagrid>
                        <ReferenceField label="Prénom" source="user" reference="users" linkType="">
                            <TextField source="givenName" />
                        </ReferenceField>
                        <ReferenceField label="Nom" source="user" reference="users" linkType="">
                            <TextField source="familyName" />
                        </ReferenceField>
                        <SelectField label="Status" source="status" choices={statusChoices} />
                        <EditButton />
                        <DeleteButton />
                    </Datagrid>
                </ReferenceArrayField>
                <AddNewMemberButton />
            </Tab>
        </TabbedShowLayout>
    </Show>
);