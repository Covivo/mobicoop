import React, { useState } from 'react';
import Alert from '@material-ui/lab/Alert';
import { makeStyles } from '@material-ui/core/styles';
import { connect } from 'react-redux';

import {
  FormWithRedirect,
  RadioButtonGroupInput,
  ReferenceInput,
  AutocompleteInput,
  useGetList,
  showNotification,
  useTranslate,
  useDataProvider,
} from 'react-admin';

import {
  LinearProgress,
  Box,
  Toolbar,
  Paper,
  Radio,
  FormControlLabel,
  RadioGroup,
  Stepper,
  Step,
  StepLabel,
  Button,
  CircularProgress,
} from '@material-ui/core';

import SolidaryUserBeneficiaryCreateFields from '../../SolidaryUserBeneficiary/SolidaryUserBeneficiaryCreateFields';
import GeocompleteInput from '../../../../components/geolocation/geocomplete';
import SolidaryQuestion from './SolidaryQuestion';
import SolidaryProofInput from './SolidaryProofInput';
import SolidaryPunctualAsk from './SolidaryPunctualAsk';
import SolidaryRegularAsk from './SolidaryRegularAsk';
import SolidaryFrequency from './SolidaryFrequency';
import SaveSolidaryAsk from './SaveSolidaryAsk';

const useStyles = makeStyles({
  layout: {
    minHeight: '80vh',
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-between',
  },
  overlayWrapper: {
    position: 'relative',
  },
  overlayProgress: {
    position: 'absolute',
    top: '50%',
    left: '50%',
    marginTop: -12,
    marginLeft: -12,
  },
});

const checkHasHomeAddress = (user) => (user.addresses || []).some((address) => !!address.home);

const LoadingOverlay = ({ loading, children }) => {
  const classes = useStyles();

  return (
    <div className={classes.overlayWrapper}>
      {children}
      {loading && <CircularProgress size={24} className={classes.overlayProgress} />}
    </div>
  );
};

const SolidaryFormStepper = ({ activeStep }) => (
  <Stepper activeStep={activeStep}>
    <Step key={1}>
      <StepLabel>Déjà enregistré ?</StepLabel>
    </Step>
    <Step key={2}>
      <StepLabel>Eligibilité</StepLabel>
    </Step>
    <Step key={3}>
      <StepLabel>Identité</StepLabel>
    </Step>
    <Step key={4}>
      <StepLabel>Trajet</StepLabel>
    </Step>
    <Step key={5}>
      <StepLabel>Horaires</StepLabel>
    </Step>
  </Stepper>
);

const SolidaryProofQuestion = () => {
  const { data: proofsList, loaded: proofsLoaded } = useGetList(
    'structure_proofs',
    { page: 1, perPage: 10 },
    { field: 'id', order: 'ASC' }
  );

  const proofs = Object.values(proofsList);

  return (
    <SolidaryQuestion question="Le demandeur est-il éligible ?">
      {proofs.length && proofsLoaded ? (
        proofs.map((p) => <SolidaryProofInput key={p.id} record={p} />)
      ) : (
        <LinearProgress />
      )}
    </SolidaryQuestion>
  );
};

const SolidarySubjectsQuestion = () => {
  const translate = useTranslate();
  const required = (message = translate('custom.alert.fieldMandatory')) => (value) =>
    value ? undefined : message;

  const { data: subjectsList, loaded: subjectsLoaded } = useGetList(
    'subjects',
    { page: 1, perPage: 10 },
    { field: 'id', order: 'ASC' }
  );

  const subjects = Object.values(subjectsList);

  return (
    <SolidaryQuestion question="Que voulez-vous faire ?">
      {subjects.length && subjectsLoaded ? (
        <RadioButtonGroupInput
          source="subject"
          label=""
          choices={subjects.map((s) => ({ id: s.id, name: s.label }))}
          validate={[required()]}
        />
      ) : (
        <LinearProgress />
      )}
    </SolidaryQuestion>
  );
};

const SolidaryFormWizard = (formProps) => {
  const classes = useStyles();
  const dataProvider = useDataProvider();

  const [hasDestinationAddress, setHasDestinationAddress] = useState(1);
  const [activeStep, setActiveStep] = useState(0);
  const [loadingNextStep, setLoadingNextStep] = useState(false);

  const { values, errors } = formProps.form.getState();
  const hasErrors = errors && Object.keys(errors).length > 0;

  const [hasRegistredUser, setHasRegistredUser] = useState(!!values.already_registered_user);

  const handleGoNext = (activeStep) => async () => {
    const state = formProps.form.getState();

    if (activeStep === 1 && state.errors.proofs) {
      formProps.showNotification("Vous devez valider l'ensemble des preuves");
      return;
    }

    // If user is registred, go to 3nd step directly
    if (activeStep === 0 && !!state.values.already_registered_user) {
      setLoadingNextStep(true);

      const hasHomeAddress = await dataProvider
        .getOne('users', { id: state.values.already_registered_user })
        .then(({ data }) => checkHasHomeAddress(data))
        .catch(() => false);

      setHasRegistredUser(hasHomeAddress);
      setLoadingNextStep(false);

      if (hasHomeAddress) {
        setActiveStep(3);
        return;
      }
    }

    setActiveStep((s) => s + 1);
  };

  const handleGoBack = (activeStep) => () => {
    const state = formProps.form.getState();

    // If user is registred, go back to first step directly
    if (activeStep <= 3 && !!state.values.already_registered_user) {
      setActiveStep(0);
      return;
    }

    return setActiveStep((s) => s - 1);
  };

  return (
    <form>
      <Paper className={classes.layout}>
        <SolidaryFormStepper activeStep={activeStep} />
        <Box
          display={activeStep === 0 ? 'flex' : 'none'}
          p="1rem"
          flexDirection="column"
          flexGrow={1}
        >
          <SolidaryQuestion question="Cherchez le demandeur s'il existe, ou passez directement à l'étape suivante.">
            <ReferenceInput
              label="Utilisateur"
              fullWidth
              source="already_registered_user"
              reference="users"
            >
              <AutocompleteInput
                allowEmpty
                optionText={(record) => `${record.givenName} ${record.familyName}`}
              />
            </ReferenceInput>
          </SolidaryQuestion>
        </Box>
        {/*
             Keep existing system but disable validation for registred user.
             This way we're able to go back to the previous system in the future
          */}
        {!hasRegistredUser && (
          <Box
            display={activeStep === 1 ? 'flex' : 'none'}
            p="1rem"
            flexDirection="column"
            flexGrow={1}
          >
            <SolidaryProofQuestion />
          </Box>
        )}
        {/*
             Keep existing system but disable validation for registred user.
             This way we're able to go back to the previous system in the future
          */}
        {!hasRegistredUser && (
          <Box
            display={activeStep === 2 ? 'flex' : 'none'}
            p="1rem"
            flexDirection="column"
            flexGrow={1}
          >
            <SolidaryUserBeneficiaryCreateFields form={formProps.form} />
          </Box>
        )}
        <Box display={activeStep === 3 ? 'flex' : 'none'} p="1rem" flexDirection="column">
          <SolidarySubjectsQuestion />
          <SolidaryQuestion question="Où faut-il aller ?">
            <RadioGroup
              value={hasDestinationAddress}
              onChange={(e) => setHasDestinationAddress(parseInt(e.target.value, 10))}
            >
              <FormControlLabel value={1} control={<Radio />} label="Quel que soit le lieu" />
              <FormControlLabel value={2} control={<Radio />} label="Une adresse" />
            </RadioGroup>
            <Box display={hasDestinationAddress === 2 ? 'flex' : 'none'}>
              <GeocompleteInput
                fullWidth
                source="destination"
                label="Adresse d'arrivée"
                validate={(a) => (a ? '' : 'Champs obligatoire')}
              />
            </Box>
          </SolidaryQuestion>
          <SolidaryQuestion question="D'où devez-vous partir ?">
            <GeocompleteInput
              fullWidth
              source="origin"
              label="Adresse de départ"
              validate={(a) => (a ? '' : 'Champs obligatoire')}
            />
          </SolidaryQuestion>
          <SolidaryQuestion question="Trajet ponctuel ?">
            <SolidaryFrequency source="frequency" label="ou trajet régulier ?" defaultValue={1} />
          </SolidaryQuestion>
        </Box>
        <Box display={activeStep === 4 ? 'flex' : 'none'} p="1rem" flexDirection="column">
          {values && values.frequency === 2 /* 2 = REGULAR */ ? (
            <SolidaryRegularAsk form={formProps.form} />
          ) : (
            <SolidaryPunctualAsk form={formProps.form} />
          )}
        </Box>
        {activeStep === 4 && hasErrors ? (
          <Alert severity="error">
            Le formulaire comporte des erreurs. Corrigez-les avant d'enregistrer.
          </Alert>
        ) : null}
        <Toolbar>
          <Box display="flex" justifyContent="flex-start" width="100%">
            {activeStep > 0 && (
              <Button variant="contained" color="default" onClick={handleGoBack(activeStep)}>
                Précédent
              </Button>
            )}
            &nbsp;
            {activeStep < 4 && (
              <LoadingOverlay loading={loadingNextStep}>
                <Button
                  disabled={loadingNextStep}
                  variant="contained"
                  color="primary"
                  onClick={handleGoNext(activeStep)}
                >
                  Suivant
                </Button>
              </LoadingOverlay>
            )}
            {activeStep === 4 && (
              <SaveSolidaryAsk
                saving={formProps.saving}
                disabled={hasErrors}
                handleSubmitWithRedirect={formProps.handleSubmitWithRedirect}
              />
            )}
            {/* <DeleteButton record={formProps.record} /> */}
          </Box>
        </Toolbar>
      </Paper>
    </form>
  );
};

export default connect(undefined, { showNotification })((props) => (
  <FormWithRedirect {...props} render={SolidaryFormWizard} />
));
