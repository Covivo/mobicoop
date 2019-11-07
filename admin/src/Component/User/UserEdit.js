import React from 'react';
//import bcrypt from 'bcryptjs';

import { 
    Edit,
    TabbedForm, FormTab,
    Datagrid,
    TextInput, DisabledInput, SelectInput, DateInput,
    email,
    TextField,
    EditButton,
    Button, Link, ReferenceArrayField, BooleanField, FunctionField, DeleteButton
} from 'react-admin';

const genderChoices = [
    { id: 1, name: 'Femme' },
    { id: 2, name: 'Homme' },
    { id: 3, name: 'Autre' },
];

// Edit
const AddNewAddressButton = ({ record }) => (
    <Button
        component={Link}
        to={{
            pathname: `/addresses/create`,
            search: `?user=${record.id}`
        }}
        label="Ajouter une adresse"
    >
    </Button>
);

export const UserEdit = (props) => (
    <Edit {...props} title="Utilisateurs > éditer">
        <TabbedForm>
            <FormTab label="Identité">
                <DisabledInput source="originId" label="ID"/>
                <TextInput source="givenName" label="Prénom"/>
                <TextInput source="familyName" label="Nom"/>
                <SelectInput source="gender" label="Sexe" choices={genderChoices} />
                <TextInput source="email" label="Email" validate={ email() } />
                <DateInput source="birthDate" label="Date de naissance" />
                <TextInput source="telephone" label="Téléphone"/>
            </FormTab>
            <FormTab label="Préférences">

            </FormTab>
            <FormTab label="Adresses">
                <ReferenceArrayField source="addresses" reference="addresses" addLabel={false}>
                    <Datagrid>
                        <BooleanField source="home" label="Domicile" />
                        <TextField source="name" label="Nom" />
                        <FunctionField label="Address" render={address => ((address.houseNumber && address.street) ? (address.houseNumber+ ' '+address.street) : address.streetAddress)} />
                        <TextField source="postalCode" label="Code postal" />
                        <TextField source="addressLocality" label="Ville" />
                        <TextField source="addressCountry" label="Pays" />
                        <EditButton />
                        <DeleteButton />
                    </Datagrid>
                </ReferenceArrayField>
                <AddNewAddressButton />
            </FormTab>
        </TabbedForm>
    </Edit>
);