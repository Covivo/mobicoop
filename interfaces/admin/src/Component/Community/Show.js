import React from 'react';
import { Link, Show, TabbedShowLayout, TextField, DateField, EditButton, Button, RichTextField, SelectField, ReferenceArrayField, ReferenceField, Datagrid, Tab } from 'react-admin';

const choices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Accepté' },
    { id: 2, name: 'Refusé' },
];

const AddNewMemberButton = ({ record }) => (
    <Button
        component={Link}
        to={{
            pathname: `/community_users/${record.originId}/create`,
            state: { community_id: record.originId }
        }}
        label="Ajouter un membre"
    >
    </Button>
);

export const CommunityShow = (props) => (
    <Show { ...props }>
        <TabbedShowLayout>
            <Tab label="Détails">
                <TextField source="originId" label="ID"/>
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
                        <SelectField label="Status" source="status" choices={choices} />
                        <EditButton />
                    </Datagrid>
                </ReferenceArrayField>
                <AddNewMemberButton />
            </Tab>
        </TabbedShowLayout>
    </Show>
);