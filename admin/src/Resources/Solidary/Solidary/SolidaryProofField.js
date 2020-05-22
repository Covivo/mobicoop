import React, {useState} from 'react'
import {
    SelectInput,
    TextInput,
    BooleanInput,
    FileInput,
    FileField,
} from 'react-admin'
import {LinearProgress, Box, Toolbar, Paper, Radio, FormControlLabel, RadioGroup, Stepper, Step, StepLabel, Button} from '@material-ui/core'
import { makeStyles } from '@material-ui/core/styles'

const SolidaryProofField = ({proof, ...rest}) => {
    if (proof.checkbox) {
        return <BooleanInput label={proof.label} source={proof.id} {...rest} />
    }
    if (proof.input) {
        return <TextInput label={proof.label} source={proof.id} {...rest} />
    }
    if (proof.selectbox) {
        const selectboxLabels   = proof.options.split(";")
        const selectboxIds      = proof.acceptedValues.split(";").map( (v, i) => ({id:v, name:selectboxLabels[i]}))
        return <SelectInput label={proof.label} source={proof.id} choices={selectboxIds} {...rest}/>
    }
    if (proof.file) {
        return <FileInput source={proof.id} label={proof.label} accept="application/pdf" {...rest}>
                    <FileField source="src" title="title" />
                </FileInput>
    }

    return null

}

export default SolidaryProofField
