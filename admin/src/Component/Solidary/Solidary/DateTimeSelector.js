import React, {useState} from 'react'
import {
    DateTimeInput,
} from 'react-admin'
import {FormControlLabel, RadioGroup, Radio, Box} from '@material-ui/core'
import { makeStyles } from '@material-ui/core/styles'


const useStyles = makeStyles({
    invisible: { display:"none" },
})

/*

choices = [
    { id:0, label="A une date fixe",  offsetHour=0, offsetDays=7}
    { id:1, label="Dans la semaine", offsetHour=0, offsetDays=7},
    { id:2, label="Dans le mois", offsetHour=0, offsetDays=14}
]
I missed Typescript
*/

const DateTimeSelector = ({form, fieldnameStart, fieldnameEnd, choices, initialChoice}) => {
    const classes = useStyles()
    const [choice, setChoice]   = useState(choices[initialChoice])

    const setOffset = (hours, days, fieldname) => {
        const now           = new Date()
        const nowAndDays    = new Date(now.setDate(now.getDate() + days || 0)) 
        const nowAndHours   = new Date(nowAndDays.setDate(nowAndDays.getHours() + hours || 0)) 
        form && fieldname && form.change(fieldname, nowAndHours)
    } 

    const handleChange = value => {
        const newChoice = choices[value]
        if (newChoice.id) {
            // relative date
            setOffset(0, 0, fieldnameStart)
            setOffset(newChoice.offsetHour, newChoice.offsetDays, fieldnameEnd)
        } else {
            // Absolute date
            form && fieldnameEnd && form.change(fieldnameEnd, null)
        }
        
        setChoice(newChoice)
    }

    return (
        <Box display="flex">
            <RadioGroup value={choice.id} onChange={e => handleChange(e.target.value) }>
                { choices.map(c => <FormControlLabel key={c.id} value={c.id} control={<Radio />} label={c.label} /> )}
            </RadioGroup>
            <Box>
                <div className={choice.id && classes.invisible}><DateTimeInput source={fieldnameStart} initialValue={new Date()} /></div>
                <div className={classes.invisible}><DateTimeInput source={fieldnameEnd} initialValue={new Date()} /></div>
            </Box>
        </Box>
        
    )
}

export default DateTimeSelector
