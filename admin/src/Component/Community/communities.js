import React from 'react';
import { 
    Create, Edit, List, Show, 
    Tab, TabbedShowLayout, 
    Link, 
    Datagrid,
    SimpleForm, required,
    DisabledInput, TextInput, DateInput, BooleanInput, ReferenceInput, SelectInput,
    Button, ShowButton, EditButton, DeleteButton,
    BooleanField, TextField, DateField, RichTextField, SelectField, ReferenceArrayField, ReferenceField,
    Filter
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const userId = `/users/${localStorage.getItem('id')}`;
const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Accepté' },
    { id: 2, name: 'Refusé' },
];

// List
const CommunityFilter = (props) => (
    <Filter {...props}>
        <TextInput source="name" label="Nom" alwaysOn />
    </Filter>
);
const CommunityPanel = ({ id, record, resource }) => (
    <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
);
export const CommunityList = (props) => (
    <List {...props} title="Communautés > liste" perPage={ 25 } filters={<CommunityFilter />} sort={{ field: 'originId', order: 'ASC' }}>
        <Datagrid expand={<CommunityPanel />}>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <BooleanField source="membersHidden" label="Membres masqués" sortable={false} />
            <BooleanField source="proposalsHidden" label="Annonces masquées" sortable={false} />
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
                <TextField source="originId" label="ID"/>
                <TextField source="name" label="Nom"/>
                <BooleanField source="membersHidden" label="Membres masqués" />
                <BooleanField source="proposalsHidden" label="Annonces masquées" />
                <TextField source="description" label="Description"/>
                <RichTextField source="fullDescription" label="Description complète"/>
                <DateField source="createdDate" label="Date de création"/>
                <EditButton />
            </Tab>
            <Tab label="Membres" path="members">
                <ReferenceArrayField source="communityUsers" reference="community_users" addLabel={false}>
                    <Datagrid>
                        <ReferenceField source="user" label="Prénom" reference="users" linkType="">
                            <TextField source="givenName" />
                        </ReferenceField>
                        <ReferenceField source="user" label="Nom" reference="users" linkType="">
                            <TextField source="familyName" />
                        </ReferenceField>
                        <SelectField source="status" label="Statut" choices={statusChoices} />
                        <EditButton />
                        <DeleteButton />
                    </Datagrid>
                </ReferenceArrayField>
                <AddNewMemberButton />
            </Tab>
        </TabbedShowLayout>
    </Show>
);

// Create
export const CommunityCreate = (props) => (
    <Create { ...props } title="Communautés > ajouter">
        <SimpleForm>
            <ReferenceInput source="user" label="Créateur" reference="users" defaultValue={userId}>
                <SelectInput optionText={userOptionRenderer}/>
            </ReferenceInput>
            <TextInput source="name" label="Nom" validate={required()}/>
            <BooleanInput source="membersHidden" label="Membres masqués" />
            <BooleanInput source="proposalsHidden" label="Annonces masquées" />
            <TextInput source="description" label="Description" validate={required()}/>
            <RichTextInput source="fullDescription" label="Description complète" validate={required()}/>
        </SimpleForm>
    </Create>
);

// Edit
export const CommunityEdit = (props) => (
    <Edit {...props } title="Communautés > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <ReferenceInput source="user" label="Créateur" reference="users">
                <SelectInput optionText={userOptionRenderer} />
            </ReferenceInput>
            <TextInput source="name" label="Nom" validate={required()}/>
            <BooleanInput source="membersHidden" label="Membres masqués"  />
            <BooleanInput source="proposalsHidden" label="Annonces masquées" />
            <TextInput source="description" label="Description" validate={required()}/>
            <RichTextInput source="fullDescription" label="Description complète" validate={required()} />
            <DateInput disabled source="createdDate" label="Date de création"/>
        </SimpleForm>
    </Edit>
);