import React  from 'react'
import { DateInput} from 'react-admin-date-inputs'
import frLocale from "date-fns/locale/fr"
import {
    TextInput, SelectInput,
    email, regex,BooleanInput,useTranslate, 
} from 'react-admin'

import { makeStyles } from '@material-ui/core/styles'
import {Box} from '@material-ui/core'

const useStyles = makeStyles({
    spacedHalfwidth: { maxWidth:"400px", marginBottom:"0.5rem" },
});

const SolidaryUserBeneficiaryCreateFields = props => {
    const classes = useStyles()
    const translate = useTranslate()
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

    const validateRequired = [required()];
    const paswwordRules = [required(),minPassword(),upperPassword,lowerPassword,numberPassword];
    const emailRules = [required(), email() ];

    return (
        <Box display="flex" flexDirection="column" alignItems="center" width="100%">
        <TextInput fullWidth required source="email" label={translate('custom.label.user.email')} validate={ emailRules } className={classes.spacedHalfwidth} />
        <TextInput fullWidth required source="password" label={translate('custom.label.user.password')} type="password" validate={ paswwordRules } className={classes.spacedHalfwidth}/>

        <TextInput fullWidth required source="familyName" label={translate('custom.label.user.familyName')} validate={ validateRequired } className={classes.spacedHalfwidth} />
        <TextInput fullWidth required source="givenName" label={translate('custom.label.user.givenName')} validate={ validateRequired } className={classes.spacedHalfwidth}/>
        <SelectInput fullWidth required source="gender" label={translate('custom.label.user.gender')} choices={genderChoices} validate={ validateRequired } className={classes.spacedHalfwidth}/>

        <DateInput fullWidth required source="birthDate" label={translate('custom.label.user.birthDate')} validate={[required()]} options={{ format: 'dd/MM/yyyy' }} providerOptions={{  locale: frLocale }} className={classes.spacedHalfwidth}/>
        <TextInput fullWidth required source="telephone" label={translate('custom.label.user.telephone')} validate={ validateRequired } className={classes.spacedHalfwidth}/>

        <BooleanInput fullWidth label={translate('custom.label.user.newsSubscription',{ instanceName: instance })} source="newsSubscription" className={classes.spacedHalfwidth} />
        </Box>
    )
}

export default SolidaryUserBeneficiaryCreateFields
