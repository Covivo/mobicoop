import React, {useEffect, useState} from 'react';
import GeocompleteInput from "../Utilities/geocomplete";

//import bcrypt from 'bcryptjs';

import {
    Edit,
    TabbedForm, FormTab,
    TextInput, SelectInput, DateInput,
    email, regex,Button, useDataProvider,ReferenceArrayInput, SelectArrayInput
} from 'react-admin';

const UserEdit = props => {

    const required = (message = 'Champ requis') =>
        value => value ? undefined : message;

    const minPassword = (message = 'Au minimum 8 caractères') =>
        value => value && value.length >= 8 ? undefined  : message;

    const upperPassword = regex(/^(?=.*[A-Z]).*$/ , 'Au minimum 1 majuscule' );
    const lowerPassword = regex(/^(?=.*[a-z]).*$/ , 'Au minimum 1 minuscule' );
    const numberPassword = regex(/^(?=.*[0-9]).*$/ , 'Au minimum 1 chiffre' );

    const validateUserCreation = (values) => {
        const errors = {};
        if (!values.firstName) {
            errors.firstName = ['The firstName is required'];
        }
        return errors
    };

    const genderChoices = [
        { id: 1, name: 'Femme' },
        { id: 2, name: 'Homme' },
        { id: 3, name: 'Autre' },
    ];
    const smoke = [
        {id : 0, name : 'Je ne fume pas'},
        {id : 1, name : 'Je ne fume pas en voiture'},
        {id : 2, name : 'Je fume'},
    ];
    const musique = [
        {id : false, name : 'Je préfère rouler sans fond sonore'},
        {id : true, name : 'J’écoute la radio ou de la musique'},
    ];

    const bavardage = [
        {id : false, name : 'Je ne suis pas bavard'},
        {id : true, name : 'Je discute'},
    ];


    const validateRequired = [required()];
    const paswwordRules = [required(),minPassword(),upperPassword,lowerPassword,numberPassword];

    return (
        <Edit { ...props } title="Utilisateurs > Editer">
            <TabbedForm >
                <FormTab label="Identité">
                    <TextInput required source="email" label="Email" validate={ email() } />

                    <TextInput required source="telephone" label="Téléphone" validate={ validateRequired }/>
                    <TextInput required source="givenName" label="Prénom" validate={ validateRequired }/>
                    <TextInput required source="familyName" label="Nom" validate={ validateRequired }/>
                    <SelectInput required source="gender" label="Civilité" choices={genderChoices} validate={ validateRequired }/>
                    <DateInput required source="birthDate" label="Date de naissance" validate={ validateRequired } />

                    <ReferenceArrayInput  label='Roles' source="userRoles" reference="roles" >
                        <SelectArrayInput optionText="title" />
                    </ReferenceArrayInput>

                </FormTab>
                <FormTab label="Préférences">
                    <SelectInput source="smoke" label="En ce qui concerne le tabac en voiture" choices={smoke} />
                    <SelectInput source="music" label="En ce qui concerne la musique en voiture" choices={musique} />
                    <TextInput source="musicFavorites" label="Radio et/musique préférées "/>
                    <SelectInput source="chat" label="En ce qui concerne le bavardage en voiture" choices={bavardage} />
                    <TextInput source="chatFavorites" label="Sujets préférés "/>
                </FormTab>
                <FormTab label="Adresses">
                    <GeocompleteInput source="addresses" label="Adresse" validate={required()}/>
                </FormTab>

            </TabbedForm>
        </Edit>
    );

};
export default UserEdit

