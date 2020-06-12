import React from 'react';
import { DateInput } from 'react-admin-date-inputs';
import frLocale from 'date-fns/locale/fr';
import { makeStyles } from '@material-ui/core/styles';

import {
  Edit,
  TabbedForm,
  FormTab,
  TextInput,
  SelectInput,
  email,
  BooleanInput,
  FunctionField,
  useTranslate,
} from 'react-admin';

import GeocompleteInput from '../../components/geolocation/geocomplete';
import { addressRenderer } from '../../utils/renderers';
import GestionRoles from './GestionRoles';

const useStyles = makeStyles({
  spacedHalfwidth: {
    width: '45%',
    marginBottom: '1rem',
    display: 'inline-flex',
    marginRight: '1rem',
  },
  footer: { marginTop: '2rem' },
});

const UserEdit = (props) => {
  const classes = useStyles();
  const translate = useTranslate();
  const instance = process.env.REACT_APP_INSTANCE_NAME;
  const required = (message = translate('custom.alert.fieldMandatory')) => (value) =>
    value ? undefined : message;

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
  const emailRules = [required(), email()];

  return (
    <Edit {...props} title={translate('custom.label.user.title.edit')}>
      <TabbedForm initialValues={{ news_subscription: true }}>
        <FormTab label={translate('custom.label.user.indentity')}>
          <TextInput
            fullWidth
            required
            source="email"
            label={translate('custom.label.user.email')}
            validate={emailRules}
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
            source="news_subscription"
            formClassName={classes.spacedHalfwidth}
          />
          <SelectInput
            fullWidth
            source="phoneDisplay"
            label={translate('custom.label.user.phoneDisplay.visibility')}
            choices={phoneDisplay}
            formClassName={classes.spacedHalfwidth}
          />
          <FunctionField
            label={translate('custom.label.user.currentAdresse')}
            source="addresses"
            render={({ addresses }) => addresses.map(addressRenderer)}
          />
          <GeocompleteInput
            source="addresses[0]"
            label={translate('custom.label.user.newsAdresse')}
            validate={required()}
            formClassName={classes.fullwidth}
          />
          <BooleanInput
            initialValue={true}
            label={translate('custom.label.user.accepteReceiveEmail')}
            source="newsSubscription"
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
    </Edit>
  );
};
export default UserEdit;
