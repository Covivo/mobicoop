import React from 'react';
import GeocompleteInput from "../Utilities/geocomplete";

import {
    Create,
    TabbedForm, FormTab,
    TextInput, SelectInput, DateInput,
    email, regex, ReferenceArrayInput, SelectArrayInput,BooleanInput,ReferenceInput
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles'

const useStyles = makeStyles({
    spacedHalfwidth: { width:"45%", marginBottom:"1rem", display:'inline-flex', marginRight: '1rem' },
    footer: { marginTop:"2rem" },
});

const UserCreate = props => {
    const classes = useStyles()
    const required = (message = 'Champ requis') =>
            value => value ? undefined : message;

    const minPassword = (message = 'Au minimum 8 caractères') =>
        value => value && value.length >= 8 ? undefined  : message;

    const upperPassword = regex(/^(?=.*[A-Z]).*$/ , 'Au minimum 1 majuscule' );
    const lowerPassword = regex(/^(?=.*[a-z]).*$/ , 'Au minimum 1 minuscule' );
    const numberPassword = regex(/^(?=.*[0-9]).*$/ , 'Au minimum 1 chiffre' );

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
    const emailRules = [required(), email() ];
    const validateUserCreation = values => values.address ? {} :  ({ address : "L'adresse est obligatoire" })

    return (
        <Create { ...props } title="Utilisateurs > ajouter">
            <TabbedForm validate={validateUserCreation} initialValues={{news_subscription:true}} >
                <FormTab label="Identité">
                    <TextInput fullWidth required source="email" label="Email" validate={ emailRules } formClassName={classes.spacedHalfwidth} />
                    <TextInput fullWidth required source="password" label="Mot de passe" type="password" validate={ paswwordRules } formClassName={classes.spacedHalfwidth}/>

                    <TextInput fullWidth required source="familyName" label="Nom" validate={ validateRequired } formClassName={classes.spacedHalfwidth} />
                    <TextInput fullWidth required source="givenName" label="Prénom" validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>
                    <SelectInput required source="gender" label="Civilité" choices={genderChoices} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>
                    <DateInput required source="birthDate" label="Date de naissance" validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>

                    <TextInput required source="telephone" label="Téléphone" validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>

                    <BooleanInput fullWidth label="Recevoir les actualités du service Ouestgo (informations utiles pour covoiturer, et nouveaux services ou nouvelles fonctionnalités)" source="news_subscription" formClassName={classes.spacedHalfwidth} />

                    <GeocompleteInput fullWidth source="addresses" label="Adresse" validate={required("L'adresse est obligatoire")}/>

                    <ReferenceArrayInput required label="Droits d'accès" source="userRoles" reference="roles" validate={ validateRequired } formClassName={classes.footer}>
                        <SelectArrayInput optionText="title" />
                    </ReferenceArrayInput>

                    <ReferenceInput label='Territoires' source="userTerritories" reference="territories">
                        <SelectInput optionText="name" />
                    </ReferenceInput>

                    <BooleanInput initialValue={true} label="Accepte de recevoir les emails" source="newsSubscription" />

                </FormTab>
                <FormTab label="Préférences">
                    <SelectInput fullWidth source="music" label="En ce qui concerne la musique en voiture" choices={musique} formClassName={classes.spacedHalfwidth}/>
                    <TextInput fullWidth source="musicFavorites" label="Radio et/musique préférées " formClassName={classes.spacedHalfwidth}/>
                    <SelectInput fullWidth source="chat" label="En ce qui concerne le bavardage en voiture" choices={bavardage} formClassName={classes.spacedHalfwidth}/>
                    <TextInput fullWidth source="chatFavorites" label="Sujets préférés " formClassName={classes.spacedHalfwidth}/>
                    <SelectInput fullWidth source="smoke" label="En ce qui concerne le tabac en voiture" choices={smoke} formClassName={classes.spacedHalfwidth}/>
                </FormTab>


            </TabbedForm>
        </Create>
    );

};
export default UserCreate

