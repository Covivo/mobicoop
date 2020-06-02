import React, { useState, useCallback } from 'react';

import {
  FormWithRedirect,
  SaveButton,
  CheckboxGroupInput,
  ReferenceInput,
  AutocompleteInput,
  useGetList,
  useCreate,
  useNotify,
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
} from '@material-ui/core';
import { useForm } from 'react-final-form';
import { makeStyles } from '@material-ui/core/styles';
import SolidaryUserBeneficiaryCreateFields from '../SolidaryUserBeneficiary/SolidaryUserBeneficiaryCreateFields';
import GeocompleteInput from '../../../components/geolocation/geocomplete';
import SolidaryQuestion from './SolidaryQuestion';
import SolidaryProofField from './SolidaryProofField';
import SolidaryPunctualAsk from './SolidaryPunctualAsk';
import SolidaryRegularAsk from './SolidaryRegularAsk';
import SolidaryStatus from './SolidaryStatus';
import SolidaryFrequency from './SolidaryFrequency';

const useStyles = makeStyles({
  layout: {
    minHeight: '80vh',
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-between',
  },
});

const SaveSolidaryAsk = ({ handleSubmitWithRedirect, ...props }) => {
  const form = useForm();

  const handleClick = useCallback(() => {
    // Alter proofs
    console.log('Saving :', form.getState().values);
    console.log('handleSubmitWithRedirect:', handleSubmitWithRedirect);
    const proofs = form.getState().values.proofs;
    form.change('proofs', proofs && Object.keys(proofs).map((k) => ({ id: k, value: proofs[k] })));
    handleSubmitWithRedirect('list');
  }, [form]);

  // override handleSubmitWithRedirect with custom logic
  return <SaveButton {...props} handleSubmitWithRedirect={handleClick} />;
};

const SolidaryForm = (props) => {
  const classes = useStyles();

  // List of proofs
  const { data: proofsList, loaded: proofsLoaded } = useGetList(
    'structure_proofs',
    { page: 1, perPage: 10 },
    { field: 'id', order: 'ASC' }
  );
  const proofs = Object.values(proofsList);
  // List of subjects
  const { data: subjectsList, loaded: subjectsLoaded } = useGetList(
    'subjects',
    { page: 1, perPage: 10 },
    { field: 'id', order: 'ASC' }
  );
  const subjects = Object.values(subjectsList);

  const [hasDestinationAddress, setHasDestinationAddress] = useState(1);
  const [activeStep, setActiveStep] = useState(0);

  return (
    <FormWithRedirect
      {...props}
      render={(formProps) => {
        const formState = formProps.form.getState();
        const frequencyRegular = formState.values && formState.values.frequency === 2;

        console.log('state :', formState);
        console.log('formProps :', formProps);

        return (
          // here starts the custom form layout
          <form>
            <Paper className={classes.layout}>
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
              <Box
                display={activeStep === 1 ? 'flex' : 'none'}
                p="1rem"
                flexDirection="column"
                flexGrow={1}
              >
                <SolidaryQuestion question="Le demandeur est-il éligible ?">
                  {proofs && proofs.length && proofsLoaded ? (
                    proofs.map((p) => <SolidaryProofField key={p.id} proof={p} />)
                  ) : (
                    <LinearProgress />
                  )}
                </SolidaryQuestion>
              </Box>
              <Box
                display={activeStep === 2 ? 'flex' : 'none'}
                p="1rem"
                flexDirection="column"
                flexGrow={1}
              >
                <SolidaryUserBeneficiaryCreateFields form={formProps.form} />
              </Box>
              <Box display={activeStep === 3 ? 'flex' : 'none'} p="1rem" flexDirection="column">
                <SolidaryQuestion question="Que voulez-vous faire ?">
                  {subjects && subjects.length && subjectsLoaded ? (
                    <CheckboxGroupInput
                      source="subject"
                      label=""
                      choices={subjects.map((s) => ({ id: s.id, name: s.label }))}
                    />
                  ) : (
                    <LinearProgress />
                  )}
                </SolidaryQuestion>

                <SolidaryQuestion question="Ou faut-il aller ?">
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

                <SolidaryQuestion question="D'ou devez-vous partir ?">
                  <GeocompleteInput
                    fullWidth
                    source="origin"
                    label="Adresse de départ"
                    validate={(a) => (a ? '' : 'Champs obligatoire')}
                  />
                </SolidaryQuestion>

                <SolidaryQuestion question="Trajet ponctuel ?">
                  <SolidaryFrequency
                    source="frequency"
                    label="ou trajet régulier ?"
                    defaultValue={1}
                  />
                </SolidaryQuestion>
              </Box>
              <Box display={activeStep === 4 ? 'flex' : 'none'} p="1rem" flexDirection="column">
                {frequencyRegular ? (
                  <SolidaryRegularAsk form={formProps.form} />
                ) : (
                  <SolidaryPunctualAsk form={formProps.form} />
                )}
                <SolidaryStatus defaultValue={0} />
              </Box>

              <Toolbar>
                <Box display="flex" justifyContent="flex-start" width="100%">
                  {activeStep > 0 && (
                    <Button
                      variant="contained"
                      color="default"
                      onClick={() => setActiveStep((s) => s - 1)}
                    >
                      Précédent
                    </Button>
                  )}
                  &nbsp;
                  {activeStep < 4 && (
                    <Button
                      variant="contained"
                      color="primary"
                      onClick={() => setActiveStep((s) => s + 1)}
                    >
                      Suivant
                    </Button>
                  )}
                  {activeStep === 4 && (
                    <SaveSolidaryAsk
                      saving={formProps.saving}
                      handleSubmitWithRedirect={formProps.handleSubmitWithRedirect}
                    />
                  )}
                  {/* <DeleteButton record={formProps.record} /> */}
                </Box>
              </Toolbar>
            </Paper>
          </form>
        );
      }}
    />
  );
};

export default SolidaryForm;
