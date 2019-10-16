import React from 'react';
//import bcrypt from 'bcryptjs';

import { 
    Create,
    TabbedForm, FormTab,
    TextInput, SelectInput, DateInput,
    email,
} from 'react-admin';

const genderChoices = [
    { id: 1, name: 'Femme' },
    { id: 2, name: 'Homme' },
    { id: 3, name: 'Autre' },
];

export const UserCreate = (props) => (
    <Create { ...props } title="Utilisateurs > ajouter">
        <TabbedForm>
            <FormTab label="Identité">
                <TextInput source="givenName" label="Prénom"/>
                <TextInput source="familyName" label="Nom"/>
                <SelectInput source="gender" label="Sexe" choices={genderChoices} />
                <TextInput source="email" label="Email" validate={ email() } />
                <DateInput source="birthDate" label="Date de naissance" />
                <TextInput source="telephone" label="Téléphone"/>
            </FormTab>
            <FormTab label="Sécurité">
                <TextInput source="password" label="Mot de passe" type="password"/>
            </FormTab>
            <FormTab label="Préférences">

            </FormTab>
            <FormTab label="Adresses">

            </FormTab>
        </TabbedForm>
</Create>
);