import React, { useEffect, useState, useCallback } from 'react';
import PropTypes from 'prop-types';
import { DateInput } from 'react-admin-date-inputs';
import frLocale from 'date-fns/locale/fr';
import { subYears } from 'date-fns';
import { useField } from 'react-final-form';
import { makeStyles } from '@material-ui/core/styles';
import { Box, CircularProgress, TextField } from '@material-ui/core';

import {
  TextInput,
  SelectInput,
  email,
  BooleanInput,
  useTranslate,
  useDataProvider,
  useNotify,
} from 'react-admin';

import GeocompleteInput from '../../../components/geolocation/geocomplete';

const emailNoticeStyle = {
  maxWidth: 400,
  marginBottom: '0.5rem',
  backgroundColor: '#eaeaea',
  padding: 5,
  borderRadius: 3,
};

const useStyles = makeStyles({
  spacedHalfwidth: { maxWidth: '400px', marginBottom: '0.5rem' },
  root: { width: '100%', maxWidth: '400px', marginBottom: '0.5rem' },
  loadingHeader: {
    display: 'flex',
    width: '100%',
    flexDirection: 'row',
    justifyContent: 'space-around',
    alignItems: 'center',
    maxWidth: '400px',
    marginBottom: '0.5rem',
  },
});

const useStylesForGeocompleteInput = makeStyles({
  root: { width: '100%', maxWidth: '400px', marginBottom: '0.5rem' },
});

const SolidaryUserBeneficiaryCreateFields = ({ form }) => {
  const classes = useStyles();
  const classesForGeocompleteInput = useStylesForGeocompleteInput();
  const translate = useTranslate();
  const instance = process.env.REACT_APP_INSTANCE_NAME;
  const [loading, setLoading] = useState(false);
  const [oldAddress, setOldAddress] = useState(null);
  // Pre-fill user data
  const {
    input: { value: userId },
  } = useField('already_registered_user');
  const dataProvider = useDataProvider();
  const notify = useNotify();
  const prefillUserData = useCallback(
    (id) => {
      if (id) {
        setLoading(true);
        dataProvider
          .getOne('users', { id })
          .then((result) => {
            if (result.data.email) {
              form.change('email', result.data.email);
              form.change('familyName', result.data.familyName);
              form.change('givenName', result.data.givenName);
              form.change('gender', result.data.gender);
              form.change('birthDate', result.data.birthDate);
              form.change('telephone', result.data.telephone);
              form.change('newsSubscription', result.data.newsSubscription || false);
              if (result.data.addresses && result.data.addresses.length) {
                const homeAddress = result.data.addresses.find((a) => a.home);
                if (homeAddress) {
                  setOldAddress(homeAddress);
                }
              }
            }
          })
          .catch((error) => notify(error.message, 'warning'))
          .finally(() => setLoading(false));
      }
    },
    [dataProvider, notify, form]
  );
  useEffect(() => {
    if (userId) {
      prefillUserData(userId);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [userId]);

  const required = (message = translate('custom.alert.fieldMandatory')) => (value) =>
    value ? undefined : message;

  const genderChoices = [
    { id: 1, name: translate('custom.label.user.choices.women') },
    { id: 2, name: translate('custom.label.user.choices.men') },
    { id: 3, name: translate('custom.label.user.choices.other') },
  ];

  const validateRequired = [required()];
  const emailRules = [email()];

  if (loading) {
    return (
      <Box display="flex" flexDirection="column" alignItems="center" width="100%">
        {loading && (
          <Box className={classes.loadingHeader}>
            <CircularProgress />
            <p>Recherche de l&lsquo;utilisateur...</p>
          </Box>
        )}
      </Box>
    );
  }

  return (
    <Box display="flex" flexDirection="column" alignItems="center" width="100%">
      {loading && (
        <Box className={classes.loadingHeader}>
          <CircularProgress />
          <p>Recherche de l&lsquo;utilisateur...</p>
        </Box>
      )}
      <TextInput
        fullWidth
        required
        source="familyName"
        label={translate('custom.label.user.familyName')}
        validate={validateRequired}
        className={classes.spacedHalfwidth}
      />
      <TextInput
        fullWidth
        required
        source="givenName"
        label={translate('custom.label.user.givenName')}
        validate={validateRequired}
        className={classes.spacedHalfwidth}
      />
      <SelectInput
        fullWidth
        required
        source="gender"
        label={translate('custom.label.user.gender')}
        choices={genderChoices}
        validate={validateRequired}
        className={classes.spacedHalfwidth}
      />
      <DateInput
        fullWidth
        required
        source="birthDate"
        label={translate('custom.label.user.birthDate')}
        validate={[required()]}
        options={{ format: 'dd/MM/yyyy', initialFocusedDate: subYears(new Date(), 18) }}
        providerOptions={{ locale: frLocale }}
        className={classes.spacedHalfwidth}
      />
      <TextInput
        fullWidth
        required
        source="telephone"
        label={translate('custom.label.user.telephone')}
        validate={validateRequired}
        className={classes.spacedHalfwidth}
      />
      <TextField
        defaultValue={
          oldAddress &&
          oldAddress.displayLabel &&
          oldAddress.displayLabel.length &&
          oldAddress.displayLabel.join(' ')
        }
        fullWidth
        disabled
        className={classes.spacedHalfwidth}
      />
      <GeocompleteInput
        fullWidth
        source="homeAddress"
        label="Nouvelle Adresse"
        validate={(a) => (a ? '' : 'Champs obligatoire')}
        classes={classesForGeocompleteInput}
      />
      <BooleanInput
        fullWidth
        label={translate('custom.label.user.newsSubscription', { instanceName: instance })}
        source="newsSubscription"
        className={classes.spacedHalfwidth}
      />
      <span style={emailNoticeStyle}>
        L'adresse email est optionnelle : une adresse email générique sera attribuée à l'utilisateur
        comme identifiant si aucune n'est renseignée, et il sera possible de lui attribuer sa vraie
        adresse email par la suite.
      </span>
      <TextInput
        fullWidth
        source="email"
        label={translate('custom.label.user.email')}
        validate={emailRules}
        className={classes.spacedHalfwidth}
      />
    </Box>
  );
};

SolidaryUserBeneficiaryCreateFields.propTypes = {
  form: PropTypes.object.isRequired,
};

export default SolidaryUserBeneficiaryCreateFields;
