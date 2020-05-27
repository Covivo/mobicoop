import React, { useState } from 'react';
import GeocompleteInput from '../../components/geolocation/geocomplete';
import GestionRoles from './GestionRoles';

import { DateInput } from 'react-admin-date-inputs';
import frLocale from 'date-fns/locale/fr';

import {
  Create,
  TabbedForm,
  FormTab,
  TextInput,
  SelectInput,
  email,
  regex,
  ReferenceArrayInput,
  SelectArrayInput,
  BooleanInput,
  ReferenceInput,
  useTranslate,
  Toolbar,
  SaveButton,
  useCreate,
  useRedirect,
  useNotify,
  useDataProvider,
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles';

const useStyles = makeStyles({
  spacedHalfwidth: {
    width: '45%',
    marginBottom: '1rem',
    display: 'inline-flex',
    marginRight: '1rem',
  },
  footer: { marginTop: '2rem' },
});

const UserCreate = (props) => {
  const classes = useStyles();
  const translate = useTranslate();
  const instance = process.env.REACT_APP_INSTANCE_NAME;

  const [territory, setTerritory] = useState();

  const required = (message = translate('custom.alert.fieldMandatory')) => (value) =>
    value ? undefined : message;

  const minPassword = (message = 'Au minimum 8 caractères') => (value) =>
    value && value.length >= 8 ? undefined : message;

  const upperPassword = regex(
    /^(?=.*[A-Z]).*$/,
    translate('custom.label.user.errors.upperPassword')
  );
  const lowerPassword = regex(
    /^(?=.*[a-z]).*$/,
    translate('custom.label.user.errors.lowerPassword')
  );
  const numberPassword = regex(
    /^(?=.*[0-9]).*$/,
    translate('custom.label.user.errors.numberPassword')
  );

  const genderChoices = [
    { id: 1, name: translate('custom.label.user.choices.women') },
    { id: 2, name: translate('custom.label.user.choices.men') },
    { id: 3, name: translate('custom.label.user.choices.other') },
  ];
  const smoke = [
    { id: 0, name: translate('custom.label.user.choices.didntSmoke') },
    { id: 1, name: translate('custom.label.user.choices.didntSmokeCar') },
    { id: 2, name: translate('custom.label.user.choices.smoke') },
  ];
  const musique = [
    { id: false, name: translate('custom.label.user.choices.withoutMusic') },
    { id: true, name: translate('custom.label.user.choices.withMusic') },
  ];

  const bavardage = [
    { id: false, name: translate('custom.label.user.choices.dontTalk') },
    { id: true, name: translate('custom.label.user.choices.talk') },
  ];

  const phoneDisplay = [
    { id: 0, name: translate('custom.label.user.phoneDisplay.forAll') },
    { id: 1, name: translate('custom.label.user.phoneDisplay.forCarpooler') },
  ];

  const validateRequired = [required()];
  const paswwordRules = [required(), minPassword(), upperPassword, lowerPassword, numberPassword];
  const emailRules = [required(), email()];
  const validateUserCreation = (values) =>
    values.address ? {} : { address: translate('custom.label.user.adresseMandatory') };

  return (
    <Create {...props} title={translate('custom.label.user.title.create')}>
      <TabbedForm validate={validateUserCreation} initialValues={{ newsSubscription: true }}>
        <FormTab label={translate('custom.label.user.indentity')}>
          <TextInput
            fullWidth
            required
            source="email"
            label={translate('custom.label.user.email')}
            validate={emailRules}
            formClassName={classes.spacedHalfwidth}
          />
          <TextInput
            fullWidth
            required
            source="password"
            label={translate('custom.label.user.password')}
            type="password"
            validate={paswwordRules}
            formClassName={classes.spacedHalfwidth}
          />

          <TextInput
            fullWidth
            required
            source="familyName"
            label={translate('custom.label.user.familyName')}
            validate={validateRequired}
            formClassName={classes.spacedHalfwidth}
          />
          <TextInput
            fullWidth
            required
            source="givenName"
            label={translate('custom.label.user.givenName')}
            validate={validateRequired}
            formClassName={classes.spacedHalfwidth}
          />
          <SelectInput
            required
            source="gender"
            label={translate('custom.label.user.gender')}
            choices={genderChoices}
            validate={validateRequired}
            formClassName={classes.spacedHalfwidth}
          />

          <DateInput
            required
            source="birthDate"
            label={translate('custom.label.user.birthDate')}
            validate={[required()]}
            options={{ format: 'dd/MM/yyyy' }}
            providerOptions={{ locale: frLocale }}
            formClassName={classes.spacedHalfwidth}
          />
          <TextInput
            required
            source="telephone"
            label={translate('custom.label.user.telephone')}
            validate={validateRequired}
            formClassName={classes.spacedHalfwidth}
          />

          <BooleanInput
            fullWidth
            label={translate('custom.label.user.newsSubscription', { instanceName: instance })}
            source="newsSubscription"
            formClassName={classes.spacedHalfwidth}
          />

          <SelectInput
            fullWidth
            source="phoneDisplay"
            label={translate('custom.label.user.phoneDisplay.visibility')}
            choices={phoneDisplay}
            formClassName={classes.spacedHalfwidth}
          />

          <GeocompleteInput
            fullWidth
            source="addresses"
            label={translate('custom.label.user.adresse')}
            validate={required(translate('custom.label.user.adresseMandatory'))}
          />
        </FormTab>
        <FormTab label={translate('custom.label.user.preference')}>
          <SelectInput
            fullWidth
            source="music"
            label={translate('custom.label.user.carpoolSetting.music')}
            choices={musique}
            formClassName={classes.spacedHalfwidth}
          />
          <TextInput
            fullWidth
            source="musicFavorites"
            label={translate('custom.label.user.carpoolSetting.musicFavorites')}
            formClassName={classes.spacedHalfwidth}
          />
          <SelectInput
            fullWidth
            source="chat"
            label={translate('custom.label.user.carpoolSetting.chat')}
            choices={bavardage}
            formClassName={classes.spacedHalfwidth}
          />
          <TextInput
            fullWidth
            source="chatFavorites"
            label={translate('custom.label.user.carpoolSetting.chatFavorites')}
            formClassName={classes.spacedHalfwidth}
          />
          <SelectInput
            fullWidth
            source="smoke"
            label={translate('custom.label.user.carpoolSetting.smoke')}
            choices={smoke}
            formClassName={classes.spacedHalfwidth}
          />
        </FormTab>

        <FormTab label={translate('custom.label.user.manageRoles')}>
          <GestionRoles />
        </FormTab>
      </TabbedForm>
    </Create>
  );
};
export default UserCreate;
