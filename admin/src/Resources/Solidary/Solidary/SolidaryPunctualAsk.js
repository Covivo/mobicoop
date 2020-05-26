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
import DateTimeSelector from './DateTimeSelector'
import SolidaryQuestion from './SolidaryQuestion'
import SolidaryNeeds from './SolidaryNeeds'

const fromDateChoices = [
    { id:0, label:"A une date fixe",  offsetHour:0, offsetDays:0},
    { id:1, label:"Dans la semaine", offsetHour:0, offsetDays:7},
    { id:2, label:"Dans la quinzaine", offsetHour:0, offsetDays:14},
    { id:3, label:"Dans le mois", offsetHour:0, offsetDays:30},
]

const fromTimeChoices = [
    { id:0, label:"A une heure fixe",  offsetHour:0, offsetDays:0},
    { id:1, label:"Entre 8h et 13h", offsetHour:5, offsetDays:0, fromHour:8},
    { id:2, label:"Entre 13h et 18h", offsetHour:5, offsetDays:0, fromHour:13},
    { id:3, label:"Entre 18h et 21h", offsetHour:3, offsetDays:0, fromHour:18},
]

const toTimeChoices = [
    { id:0, label:"A une heure fixe",  offsetHour:0, offsetDays:0},
    { id:1, label:"Une heure plus tard", offsetHour:1, offsetDays:0},
    { id:2, label:"Deux heures plus tard", offsetHour:2, offsetDays:0},
    { id:3, label:"Trois heures plus tard", offsetHour:3, offsetDays:0},
    { id:4, label:"Pas besoin qu'on me ramène",  offsetHour:0, offsetDays:0},
    
]

const useStyles = makeStyles({
    invisible: { display:"block" },
})

const RadioChoices = ({choices, initialChoice=0, callback}) => {
    const [choice, setChoice]   = useState(choices[initialChoice])
    return (
        <RadioGroup value={choice.id} onChange={e => callback(e.target.value) }>
            { choices.map(c => <FormControlLabel key={c.id} value={c.id} control={<Radio />} label={c.label} /> )}
        </RadioGroup>
    )
}

const SolidaryPunctualAsk = ({form}) => {
    const classes = useStyles()
    const now   = new Date() 
    const today = new Date( now.getFullYear(), now.getMonth(), now.getDate(), 8 )

    return (
        <>
        <div className={classes.invisible}><DateTimeInput source="fromStartDate" /></div>
        <div className={classes.invisible}><DateTimeInput source="fromEndDate" /></div>
        <div className={classes.invisible}><DateTimeInput source="toStartDate" /></div>
        <div className={classes.invisible}><DateTimeInput source="toEndDate" /></div>

        <SolidaryQuestion question="A quelle date souhaitez-vous partir ?">
            <DateTimeSelector form={form} type="date" fieldnameStart="fromStartDate" fieldnameEnd="fromEndDate" choices={fromDateChoices} initialChoice={0} />
        </SolidaryQuestion>

        <SolidaryQuestion question="A quelle heure souhaitez-vous partir ?">
            <DateTimeSelector form={form} type="time" fieldnameStart="fromStartDate" fieldnameEnd="fromEndDate" choices={fromTimeChoices} initialChoice={0} /> 
        </SolidaryQuestion>

        

        <SolidaryQuestion question="Quand souhaitez-vous revenir ?">
            <DateTimeSelector form={form} type="datetime-local" fieldnameStart="toStartDatetime" fieldnameEnd="toEndDatetime" choices={toTimeChoices} initialChoice={0} /> 
        </SolidaryQuestion>

        <SolidaryQuestion question="Autres informations">
            <SolidaryNeeds />
        </SolidaryQuestion>
        </>
    )
}

export default SolidaryPunctualAsk

