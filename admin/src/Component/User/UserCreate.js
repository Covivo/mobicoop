import React from 'react';
import GeocompleteInput from "../Utilities/geocomplete";

import {
    Create,
    TabbedForm, FormTab,
    TextInput, SelectInput, DateInput,
    email, regex, ReferenceArrayInput, SelectArrayInput,BooleanInput,ReferenceInput,useTranslate
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles'

const useStyles = makeStyles({
    spacedHalfwidth: { width:"45%", marginBottom:"1rem", display:'inline-flex', marginRight: '1rem' },
    footer: { marginTop:"2rem" },
});

const UserCreate = props => {
    const classes = useStyles()
    const translate = useTranslate();
    const instance = process.env.REACT_APP_INSTANCE_NAME;

    const required = (message = translate('custom.alert.fieldMandatory') ) =>
            value => value ? undefined : message;

    const minPassword = (message = 'Au minimum 8 caractères') =>
        value => value && value.length >= 8 ? undefined  : message;

    const upperPassword = regex(/^(?=.*[A-Z]).*$/ , translate('custom.label.user.errors.upperPassword')  );
    const lowerPassword = regex(/^(?=.*[a-z]).*$/ , translate('custom.label.user.errors.lowerPassword')  );
    const numberPassword = regex(/^(?=.*[0-9]).*$/ , translate('custom.label.user.errors.numberPassword')  );

    const genderChoices = [
        { id: 1, name: translate('custom.label.user.choices.women') },
        { id: 2, name: translate('custom.label.user.choices.men') },
        { id: 3, name: translate('custom.label.user.choices.other') },
    ];
    const smoke = [
        {id : 0, name : translate('custom.label.user.choices.didntSmoke')},
        {id : 1, name : translate('custom.label.user.choices.didntSmokeCar')},
        {id : 2, name : translate('custom.label.user.choices.smoke')},
    ];
    const musique = [
        {id : false, name :  translate('custom.label.user.choices.withoutMusic')},
        {id : true, name : translate('custom.label.user.choices.withMusic')},
    ];

    const bavardage = [
        {id : false, name : translate('custom.label.user.choices.dontTalk')},
        {id : true, name : translate('custom.label.user.choices.talk')},
    ];

    const validateRequired = [required()];
    const paswwordRules = [required(),minPassword(),upperPassword,lowerPassword,numberPassword];
    const emailRules = [required(), email() ];
    const validateUserCreation = values => values.address ? {} :  ({ address : "L'adresse est obligatoire" })

    return (
        <Create { ...props } title={translate('custom.label.user.title.create')}>
            <TabbedForm validate={validateUserCreation} initialValues={{news_subscription:true}} >
                <FormTab label={translate('custom.label.user.indentity')}>
                    <TextInput fullWidth required source="email" label={translate('custom.label.user.email')} validate={ emailRules } formClassName={classes.spacedHalfwidth} />
                    <TextInput fullWidth required source="password" label={translate('custom.label.user.password')} type="password" validate={ paswwordRules } formClassName={classes.spacedHalfwidth}/>

                    <TextInput fullWidth required source="familyName" label={translate('custom.label.user.familyName')} validate={ validateRequired } formClassName={classes.spacedHalfwidth} />
                    <TextInput fullWidth required source="givenName" label={translate('custom.label.user.givenName')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>
                    <SelectInput required source="gender" label={translate('custom.label.user.gender')} choices={genderChoices} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>
                    <DateInput required source="birthDate" label={translate('custom.label.user.birthDate')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>

                    <TextInput required source="telephone" label={translate('custom.label.user.telephone')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>

                    <BooleanInput fullWidth label={translate('custom.label.user.newsSubscription',{ instanceName: instance })} source="news_subscription" formClassName={classes.spacedHalfwidth} />

                    <GeocompleteInput fullWidth source="addresses" label={translate('custom.label.user.adresse')} validate={required("L'adresse est obligatoire")}/>

                    <ReferenceArrayInput required label={translate('custom.label.user.roles')} source="userAuthAssignments" reference="permissions/roles" validate={ validateRequired } formClassName={classes.footer}>
                        <SelectArrayInput optionText="name" />
                    </ReferenceArrayInput>

                    <ReferenceInput label={translate('custom.label.user.territory')} source="userTerritories" reference="territories">
                        <SelectInput optionText="name" />
                    </ReferenceInput>

                    <BooleanInput initialValue={true} label={translate('custom.label.user.accepteReceiveEmail')} source="newsSubscription" />

                </FormTab>
                <FormTab label={translate('custom.label.user.preference')}>
                <SelectInput fullWidth source="music" label={translate('custom.label.user.carpoolSetting.music')} choices={musique} formClassName={classes.spacedHalfwidth}/>
                <TextInput fullWidth source="musicFavorites" label={translate('custom.label.user.carpoolSetting.musicFavorites')} formClassName={classes.spacedHalfwidth}/>
                <SelectInput fullWidth source="chat" label={translate('custom.label.user.carpoolSetting.chat')} choices={bavardage} formClassName={classes.spacedHalfwidth}/>
                <TextInput fullWidth source="chatFavorites" label={translate('custom.label.user.carpoolSetting.chatFavorites')} formClassName={classes.spacedHalfwidth}/>
                <SelectInput fullWidth source="smoke" label={translate('custom.label.user.carpoolSetting.smoke')} choices={smoke} formClassName={classes.spacedHalfwidth}/>
                </FormTab>


            </TabbedForm>
        </Create>
    );

};
export default UserCreate
