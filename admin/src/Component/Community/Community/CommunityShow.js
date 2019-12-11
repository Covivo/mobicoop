import React from 'react';
import { 
    Show, 
    Tab, TabbedShowLayout, 
    Link, 
    Datagrid,
    Button, EditButton, DeleteButton,
    BooleanField, TextField, DateField, RichTextField, SelectField, ReferenceArrayField, ReferenceField, FunctionField
} from 'react-admin';

const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Membres' },
    { id: 2, name: 'Modérateur' },
    { id: 3, name: 'Refusé' },
];
const validationChoices = [
    { id: 0, name: 'Validation automatique' },
    { id: 1, name: 'Validation manuelle' },
    { id: 2, name: 'Validation par le domaine' },
];

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

const addressRenderer = address => `${address.displayLabel[0]} - ${address.displayLabel[1]}`;

export const CommunityShow = (props) => (
    <Show { ...props } title="Communautés > afficher">
        <TabbedShowLayout>
            <Tab label="Détails">
                <TextField source="originId" label="ID"/>
                <TextField source="name" label="Nom"/>
                <ReferenceField source="address" label="Adresse" reference="addresses" linkType="">
                    <FunctionField render={addressRenderer} />
                </ReferenceField>
                <BooleanField source="membersHidden" label="Membres masqués" />
                <BooleanField source="proposalsHidden" label="Annonces masquées" />
                <SelectField source="validationType" label="Type de validation" choices={validationChoices} />
                <TextField source="domain" label="Nom de domaine"/>
                <TextField source="description" label="Description"/>
                <RichTextField source="fullDescription" label="Description complète"/>
                <DateField source="createdDate" label="Date de création"/>
                <EditButton />
            </Tab>
            <Tab label="Membres et Modérateurs" path="members">
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