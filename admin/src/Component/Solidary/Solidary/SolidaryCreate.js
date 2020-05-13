import React, {useState} from 'react'
import {
    FormWithRedirect,
    DateTimeInput,
    SelectArrayInput,
    TextInput,
    SaveButton,
    CheckboxGroupInput,
    BooleanInput,
    ReferenceInput,
    AutocompleteInput,
    useGetList,
} from 'react-admin'
import {LinearProgress, Box, Toolbar, Paper, Radio, FormControlLabel, RadioGroup, Stepper, Step, StepLabel, Button} from '@material-ui/core'
import { makeStyles } from '@material-ui/core/styles'
import SolidaryUserBeneficiaryCreateFields from '../SolidaryUserBeneficiary/SolidaryUserBeneficiaryCreateFields'
import GeocompleteInput from '../../Utilities/geocomplete'
import DateTimeSelector from './DateTimeSelector'
import SolidaryQuestion from './SolidaryQuestion'

const ProofField = ({proof, ...rest}) => {
    if (proof.checkbox) {
        return <BooleanInput label={proof.label} source={proof.id} {...rest} />
    }
    if (proof.input) {
        return <TextInput label={proof.label} source={proof.id} {...rest} />
    }

    return null

}

const useStyles = makeStyles({
    layout: { minHeight:"80vh", display:"flex", flexDirection:"column", justifyContent:"space-between" },
});

const SolidaryCreate = props => {
    const classes = useStyles()
    const {data, error, loading, loaded}  = useGetList("structure_proofs", { page: 1 , perPage: 10 }, { field: 'id', order: 'ASC' })
    const proofs = Object.values(data)
    console.log("Proof:", proofs)

    const [hasDestinationAddress, setHasDestinationAddress] = useState(1)
    const [activeStep, setActiveStep] = useState(0)

    const fromDateTimeChoices = [
        { id:0, label:"A une date fixe",  offsetHour:0, offsetDays:0},
        { id:1, label:"Dans la semaine", offsetHour:0, offsetDays:7},
        { id:2, label:"Dans la quinzaine", offsetHour:0, offsetDays:14},
        { id:3, label:"Dans le mois", offsetHour:0, offsetDays:30},
    ]

    const toDateTimeChoices = [
        { id:0, label:"A une heure fixe",  offsetHour:0, offsetDays:0},
        { id:1, label:"Une heure plus tard", offsetHour:1, offsetDays:0},
        { id:2, label:"Deux heures plus tard", offsetHour:2, offsetDays:14},
        { id:3, label:"Trois heures plus tard", offsetHour:3, offsetDays:30},
    ]

    const save = handleSubmitWithRedirect => values => console.log("Saving :", values)

    return (
        <FormWithRedirect
            {...props}
            render={formProps => {
                console.log("formProps:", formProps)
                console.log("state :", formProps.form.getState())
                return (
                    // here starts the custom form layout
                    <form>
                        <Paper className={classes.layout}>
                            <Stepper activeStep={activeStep}>
                                <Step key={1} ><StepLabel >Déjà enregistré ?</StepLabel></Step>
                                <Step key={2} ><StepLabel >Eligibilité</StepLabel></Step>
                                <Step key={3} ><StepLabel >Identité</StepLabel></Step>
                                <Step key={4} ><StepLabel >Trajet</StepLabel></Step>
                                <Step key={5} ><StepLabel >Horaires</StepLabel></Step>
                            </Stepper>

                            <Box display={activeStep===0 ? "flex" : "none"} p="1rem" flexDirection="column" flexGrow={1}>
                                <SolidaryQuestion question="Cherchez le demandeur s'il existe, ou passez directement à l'étape suivante.">
                                    <ReferenceInput label="Utilisateur" fullWidth source="user_id" reference="users">
                                        <AutocompleteInput allowEmpty={true} optionText={record => record.givenName + " " + record.familyName} />
                                    </ReferenceInput>
                                </SolidaryQuestion>
                            </Box>
                            <Box display={activeStep===1 ? "flex" : "none"} p="1rem" flexDirection="column" flexGrow={1}>
                                <SolidaryQuestion question="Le demandeur est-il éligible ?">
                                    {proofs && proofs.length && loaded ? proofs.map(p => <ProofField key={p.id} proof={p} /> ) : <LinearProgress /> }
                                </SolidaryQuestion>
                            </Box>
                            <Box display={activeStep===2 ? "flex" : "none"} p="1rem" flexDirection="column" flexGrow={1}>
                                <SolidaryUserBeneficiaryCreateFields />
                            </Box>
                            <Box display={activeStep===3 ? "flex" : "none"} p="1rem" flexDirection="column">
                                <SolidaryQuestion question="Que voulez-vous faire ?">
                                    <CheckboxGroupInput source="object" choices={[
                                        { id: 1, name: 'Commerces' },
                                        { id: 2, name: 'Loisirs' },
                                        { id: 3, name: 'Santé' },
                                        { id: 4, name: 'Démarches administratives' },
                                        { id: 5, name: 'Emploi' },
                                        { id: 6, name: 'Autres' },
                                    ]} />
                                    <TextInput fullWidth source="additional_object" label="Autres activités ou plus de détails" />
                                </SolidaryQuestion>

                                <SolidaryQuestion question="Ou faut-il aller ?">
                                    <RadioGroup value={hasDestinationAddress} onChange={e => setHasDestinationAddress(parseInt(e.target.value)) }>
                                        <FormControlLabel value={1} control={<Radio />} label="Quel que soit le lieu" />
                                        <FormControlLabel value={2} control={<Radio />} label="Une adresse" />
                                    </RadioGroup>
                                    <Box display={hasDestinationAddress===2 ? "flex" : "none"}>
                                        <GeocompleteInput fullWidth source="addresses" label="Adresse d'arrivée" validate={a => console.log(a)} />}
                                    </Box>
                                </SolidaryQuestion>

                                <SolidaryQuestion question="D'ou devez-vous partir ?">
                                    <GeocompleteInput fullWidth source="addresses" label="Adresse de départ" validate={value => value ? undefined : "Elle est obligatoire"}/>
                                </SolidaryQuestion>
                            </Box>
                            <Box display={activeStep===4 ? "flex" : "none"} p="1rem" flexDirection="column">
                                <SolidaryQuestion question="Quand souhaitez-vous partir ?">
                                    <DateTimeSelector form={formProps.form} fieldnameStart="fromStartDatetime" fieldnameEnd="fromEndDatetime" choices={fromDateTimeChoices} initialChoice={0} />
                                </SolidaryQuestion>

                                <SolidaryQuestion question="Quand souhaitez-vous revenir ?">
                                    <DateTimeSelector form={formProps.form} fieldnameStart="toStartDatetime" fieldnameEnd="toEndDatetime" choices={toDateTimeChoices} initialChoice={0} />
                                </SolidaryQuestion>
                                
                                <SolidaryQuestion question="Autres informations">
                                    <CheckboxGroupInput source="needs" choices={[
                                        { id: 1, name: "J'ai besoin d'être accompagné jusqu'à ma porte" },
                                        { id: 2, name: "J'invite à prendre un café"},
                                        { id: 3, name: "J'ai besoin qu'on monte mes courses" },
                                    ]} />
                                    <TextInput fullWidth source="additional_needs" label="Autres précisions" />
                                </SolidaryQuestion>
                                
                            </Box>
                                            
                            <Toolbar>
                                <Box display="flex" justifyContent="flex-start" width="100%">
                                    
                                    {activeStep>0 && <Button variant="contained" color="default" onClick={()=>setActiveStep(s=>s-1)}>Précédent</Button>}
                                    &nbsp;
                                    {activeStep<4 && <Button variant="contained" color="primary" onClick={()=>setActiveStep(s=>s+1)}>Suivant</Button>}
                                    
                                    {activeStep===4 && <SaveButton
                                        saving={formProps.saving}
                                        handleSubmitWithRedirect={save(formProps.handleSubmitWithRedirect)}
                                    />}
                                    
                                    {/* <DeleteButton record={formProps.record} /> */}
                                </Box>
                            </Toolbar>

                        </Paper>
                    </form>
                )}
            }
        />
    )
}

export default SolidaryCreate

