import React, { useEffect, useState, useCallback } from 'react';
import PropTypes from 'prop-types';
import { DateInput } from 'react-admin-date-inputs';
import frLocale from 'date-fns/locale/fr';
import {
  TextInput,
  SelectInput,
  email,
  BooleanInput,
  useTranslate,
  useDataProvider,
  useNotify,
} from 'react-admin';
import { useField } from 'react-final-form';
import { makeStyles } from '@material-ui/core/styles';
import { Box, CircularProgress } from '@material-ui/core';
import GeocompleteInput from '../../../components/geolocation/geocomplete';

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
  const translate = useTranslate();
  const instance = process.env.REACT_APP_INSTANCE_NAME;
  const [loading, setLoading] = useState(false);
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
  const emailRules = [required(), email()];

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
        source="email"
        label={translate('custom.label.user.email')}
        validate={emailRules}
        className={classes.spacedHalfwidth}
      />

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
        options={{ format: 'dd/MM/yyyy' }}
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

      <GeocompleteInput
        fullWidth
        source="homeAddress"
        label="Adresse"
        validate={(a) => (a ? '' : 'Champs obligatoire')}
        classes={useStylesForGeocompleteInput()}
      />

      <BooleanInput
        fullWidth
        label={translate('custom.label.user.newsSubscription', { instanceName: instance })}
        source="newsSubscription"
        className={classes.spacedHalfwidth}
      />
    </Box>
  );
};

SolidaryUserBeneficiaryCreateFields.propTypes = {
  form: PropTypes.object.isRequired,
};

export default SolidaryUserBeneficiaryCreateFields;
